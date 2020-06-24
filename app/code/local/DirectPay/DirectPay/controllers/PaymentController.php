<?php

class DirectPay_DirectPay_PaymentController extends Mage_Core_Controller_Front_Action
{

    // The redirect action is triggered when someone places an order
    public function redirectAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'directpay', array('template' => 'directpay/redirect.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    // The response action is triggered when your gateway sends back a response after processing the customer's payment
    public function responseAction()
    {

        if ($this->getRequest()->isPost()) {
            $postBody = $this->getRequest()->getRawBody();
            $postObject = json_decode($postBody);

            $pubKeyid = Mage::getStoreConfig('payment/directpay/public_key');
            $public_key_res = openssl_pkey_get_public($pubKeyid);

            $dataString = $postObject->orderId . $postObject->trnId . $postObject->status . $postObject->desc;

            $signature = $postObject->signature;
            $signatureVerify = openssl_verify($dataString, base64_decode($signature), $public_key_res, OPENSSL_ALGO_SHA256);

            if ($signatureVerify == 1) {
                $order = Mage::getModel('sales/order');
                $order->loadByIncrementId($postObject->orderId);
                if ($postObject->status == 'SUCCESS') {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
                } else {
                    $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Gateway has authorized the payment.');
                }

                $order->sendNewOrderEmail();
                $order->setEmailSent(true);

                $order->save();

            }

            Mage::log($this->urlw);
            // Mage::getSingleton('checkout/session')->unsQuoteId();
            // Mage_Core_Controller_Varien_Action::_redirect($this->urlw, array('_secure'=> false));
        }

        // Mage::log("ff");
        // Mage::log($this->urlw);
        // Mage::log($this->getRequest()->getMethod());

        $status = $this->getRequest()->get('status');
        // $url = null;

        if ($status == 'SUCCESS') {
            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect("checkout/onepage/success", array('_secure' => false));
        } else {
            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect("checkout/onepage/failure", array('_secure' => false));
        }


        // Mage::log($this->getRequest());
    }

    // The cancel action is triggered when an order is to be cancelled
    public function cancelAction()
    {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if ($order->getId()) {
                // Flag the order as 'cancelled' and save it
                $order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
            }
        }
    }
}