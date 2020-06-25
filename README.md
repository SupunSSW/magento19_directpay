# Magento19 DirectPay Plugin
Magento version 1.9 plugin for DirectPay payment method


## Steps
* Copy the contetnts in local app folder to the magento server app directory.
* Log into magento admin pannel and navigate to **System > Configuration**.
* Under the **Sales** tab, select **Payment Methods**.
* Click on **DirectPay**. If **DirectPay** not available, try flushing magento cache from **System > Cache Management**.
* Set **Enabled** to **Yes** and fill in the empty fields. Then set **Sandbox Mode** to **Yes**.
* Click on **Save Config**.
* Flush magento cache from **System > Cache Management**.

## Usage
* Add a product from **Admin Panel**. \(**Catalog > Manage Products > Add Product**)
* Select **DirectPay payment method** at checkout.

