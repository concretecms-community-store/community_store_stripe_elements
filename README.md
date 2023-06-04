# Community Store Stripe Elements
Stripe Elements payment add-on for Community Store for Concrete CMS, using Stripes 'Payment' Element

Requires version 2.5+ of Community Store.

This payment method directly a Stripe 'Payment Element'  into Community Store's checkout flow, allowing for credit card payments as well as other payment types such as Apple Pay and Google Pay.
Customers paying with this method stay on the checkout page to complete their transaction, however credit card details are only ever directly set to Stripe's servers.

This is a Strong Customer Authentication (SCA) compliant payment method.

## Setup
Install Community Store First.

Download a 'release' zip of the add-on, unzip this to the packages folder of your Concrete CMS install (alongside the community_store folder) and install via the dashboard.

Once installed, configure the payment method through the Settings/Payments dashboard section for 'Store'. 
You will need to log into Stripe's Dashboard, and through the Developers section copy in test and live API Keys.
Additionally, you will also need to configure a webhook - details for this are displayed on the configuration form for the payment method.

## Compared with Stripe Checkout
This method is in contrast to [Stripe Checkout](https://github.com/concretecms-community-store/community_store_stripe_checkout), which redirects users to Stripe's external payment page, with the customer returning to the Concrete site after payment has been made.
