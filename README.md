# Community Store Stripe Elements
Stripe Elements payment add-on for Community Store for concrete5

This payment method directly embeds Stripe credit card processing (using Stripe Elements) into Community Store's checkout flow.
Customers paying with this method stay on the checkout page to complete their transaction, however credit card details are only ever directly set to Stripe's servers.
https://stripe.com/docs/payments/accept-a-payment?integration=elements

This method also automatically triggers and handles 3D Secure authentication when required.

This is a Strong Customer Authentication (SCA) compliant payment method.

## Setup
Install Community Store First.

Download a 'release' zip of the add-on, unzip this to the packages folder of your concrete5 install (alongside the community_store folder) and install via the dashboard.

Once installed, configure the payment method through the Settings/Payments dashboard section for 'Store'. 
You will need to log into Stripe's Dashboard, and through the Developers section copy in test and live API Keys.
 
## Considerations
Due to the transaction flow of this payment method, transactions are made and completed just _before_ orders are recorded.
This means that if a customer closes their browser _right_ at the moment between a transaction completing and Community Store receiving the final request to place the order, there is a chance that a payment will be made without a corresponding order being created.
This situation is considered very unlikely, but technically could occur. Other transaction flows used by other payment methods avoid this possibility.

In the event that this situation occurs, full contacts details of the customer (name, email, phone, addresses) are stored on the transaction record within Stripe's dashboard, and can be reviewed for customer contacting/refunding purposes.

It is also recommended leave enabled transaction notification emails, so that payments without corresponding order notifications can be noticed:
https://support.stripe.com/questions/set-up-account-email-notifications

Whilst very unlikely, if the possibility of the above situation occurring is concern to you, the recommendation would be to use the [Stripe Checkout](https://github.com/concrete5-community-store/community_store_stripe_checkout) payment method instead.


## Compared with Stripe Checkout

This method is in contrast to [Stripe Checkout](https://github.com/concrete5-community-store/community_store_stripe_checkout), which redirects users to Stripe's external payment page, with the customer returning to the concrete5 site after payment has been made.

A benefit of Stripe Checkout over this payment method is Apple Pay and Google Pay can be easily enabled, without having to change the payment method on the concrete5 site. 
Arguably, customers may also trust entering their credit card details into a branded Stripe checkout, rather than directly into a website. 

The main benefit of this Stripe Elements payment method is the simplicity of the credit card field/form, as that a customer never leaves the website to complete a transaction.

## Customisations

The initial styling of the credit card field has been set to cleanly match with Bootstrap/Elemental, and should appropriate display on most themes.
However, the styling can be customised by copying `packages/community_store_stripe_elements/elements/community_store_stripe_elements/checkout_form.php` to
`application/elements/community_store_stripe_elements/checkout_form.php` and modifying the style definition around line 24.
See the overview and documentation on how to style Stripe Elements at https://stripe.com/payments/elements
