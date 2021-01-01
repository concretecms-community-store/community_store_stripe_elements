<?php
namespace Concrete\Package\CommunityStoreStripeElements\Src\CommunityStore\Payment\Methods\CommunityStoreStripeElements;

use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;

class CommunityStoreStripeElementsPaymentMethod extends StorePaymentMethod
{
    private function getCurrencies()
    {
        return [
            'USD' => t('US Dollar'),
            'EUR' => t('Euro'),
            'GBP' => t('British Pounds Sterling'),
            'AUD' => t('Australian Dollar'),
            'BRL' => t('Brazilian Real'),
            'CAD' => t('Canadian Dollar'),
            'CLP' => t('Chilean Peso'),
            'CZK' => t('Czech Koruna'),
            'DKK' => t('Danish Krone'),
            'HKD' => t('Hong Kong Dollar'),
            'HUF' => t('Hungarian Forint'),
            'IRR' => t('Iranian Rial'),
            'ILS' => t('Israeli Shekel'),
            'JPY' => t('Japanese Yen'),
            'MYR' => t('Malaysian Ringgit'),
            'MXN' => t('Mexican Peso'),
            'NZD' => t('New Zealand Dollar'),
            'NOK' => t('Norwegian Krone'),
            'PHP' => t('Philippine Peso'),
            'PLN' => t('Polish Zloty'),
            'RUB' => t('Russian Rubles'),
            'SGD' => t('Singapore Dollar'),
            'KRW' => t('South Korean Won'),
            'SEK' => t('Swedish Krona'),
            'CHF' => t('Swiss Franc)'),
            'TWD' => t('Taiwan New Dollar'),
            'THB' => t('Thai Baht'),
            'TRY' => t('Turkish Lira'),
            'VND' => t('Vietnamese Dong'),
        ];
    }

    public function dashboardForm()
    {
        $this->set('stripeElementsMode', Config::get('community_store_stripe_elements.mode'));
        $this->set('stripeElementsCurrency', Config::get('community_store_stripe_elements.currency'));
        $this->set('stripeElementsTestPublicApiKey', Config::get('community_store_stripe_elements.testPublicApiKey'));
        $this->set('stripeElementsLivePublicApiKey', Config::get('community_store_stripe_elements.livePublicApiKey'));
        $this->set('stripeElementsTestPrivateApiKey', Config::get('community_store_stripe_elements.testPrivateApiKey'));
        $this->set('stripeElementsLivePrivateApiKey', Config::get('community_store_stripe_elements.livePrivateApiKey'));

        $this->set('form', Application::getFacadeApplication()->make("helper/form"));
        $this->set('stripeElementsCurrencies', $this->getCurrencies());
    }

    public function save(array $data = [])
    {
        Config::save('community_store_stripe_elements.mode', $data['stripeElementsMode']);
        Config::save('community_store_stripe_elements.currency', $data['stripeElementsCurrency']);
        Config::save('community_store_stripe_elements.testPublicApiKey', $data['stripeElementsTestPublicApiKey']);
        Config::save('community_store_stripe_elements.livePublicApiKey', $data['stripeElementsLivePublicApiKey']);
        Config::save('community_store_stripe_elements.testPrivateApiKey', $data['stripeElementsTestPrivateApiKey']);
        Config::save('community_store_stripe_elements.livePrivateApiKey', $data['stripeElementsLivePrivateApiKey']);
        Config::save('community_store_stripe_elements.signingSecretKey', $data['stripeElementsSigningSecretKey']);
        Config::save('community_store_stripe_elements.testSigningSecretKey', $data['stripeElementsTestSigningSecretKey']);
    }

    public function validate($args, $e)
    {
        return $e;
    }

    public function checkoutForm()
    {
        $mode = Config::get('community_store_stripe_elements.mode');
        $this->set('mode', $mode);
        $this->set('currency', Config::get('community_store_stripe_elements.currency'));

        if ($mode == 'live') {
            $this->set('publicElementsAPIKey', Config::get('community_store_stripe_elements.livePublicApiKey'));
        } else {
            $this->set('publicElementsAPIKey', Config::get('community_store_stripe_elements.testPublicApiKey'));
        }

        $pmID = StorePaymentMethod::getByHandle('community_store_stripe_elements')->getID();
        $this->set('pmID', $pmID);
    }

    public function submitPayment()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $request = $app->make(Request::class);
        $stripeToken = $request->request->get('stripeToken');

        $mode = Config::get('community_store_stripe_elements.mode');

        if ($mode == 'live') {
            $secretKey = Config::get('community_store_stripe_elements.livePrivateApiKey');
        } else {
            $secretKey = Config::get('community_store_stripe_elements.testPrivateApiKey');
        }

        $stripe = new \Stripe\StripeClient(
            $secretKey
        );

        // check that payment intent ID is real
        try {
            $paymentIntent = $stripe->paymentIntents->retrieve(
                $stripeToken,
                []
            );

        } catch (\Exception $e) {

        }

        if ($paymentIntent && $paymentIntent->id) {
            return array('error'=>0, 'transactionReference'=> $stripeToken);
        } else {
            return array('error'=>1, 'errorMessage'=>t('Invalid Transaction'), 'transactionReference'=> false);
        }

    }

    public function getPaymentMinimum()
    {
        return 0.5;
    }

    public function getName()
    {
        return 'Stripe Elements';
    }

    public function createSession()
    {
        $mode = Config::get('community_store_stripe_elements.mode');
        $this->set('currency', Config::get('community_store_stripe_elements.currency'));

        if ($mode == 'live') {
            $secretKey = Config::get('community_store_stripe_elements.livePrivateApiKey');
        } else {
            $secretKey = Config::get('community_store_stripe_elements.testPrivateApiKey');
        }

        $currency = Config::get('community_store_stripe_elements.currency');
        $currencyMultiplier = StorePrice::getCurrencyMultiplier($currency);

        $price = number_format(StoreCalculator::getGrandTotal(), 2, '.', '');

        \Stripe\Stripe::setApiKey($secretKey);

        $customer = new Customer();

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $price * $currencyMultiplier,
            'currency' => $currency,
            'receipt_email' =>  $customer->getEmail(),
            'metadata' => [t('Phone')=> $customer->getValue('billing_phone')]
        ]);



        $return = [
            'client_secret'=>$paymentIntent->client_secret,
            'email'=> $customer->getEmail(),
            'name'=> $customer->getValue('billing_first_name') . ' ' .  $customer->getValue('billing_last_name'),
            'phone'=>  $customer->getValue('billing_phone'),
            'billing_details' => [
                'city' =>$customer->getAddressValue('billing_address', 'city'),
                'country' => $customer->getAddressValue('billing_address', 'country'),
                'line1' => $customer->getAddressValue('billing_address', 'address1'),
                'line2' => $customer->getAddressValue('billing_address', 'address2'),
                'postal_code' => $customer->getAddressValue('billing_address', 'postal_code'),
                'state' => $customer->getAddressValue('billing_address', 'state_province')
            ]
        ];

        $shippingName = $customer->getValue('shipping_first_name');

        if ($shippingName) {
            $shippingDetails = [
                'name'=>$customer->getValue('shipping_first_name') . ' ' .  $customer->getValue('shipping_last_name'),
                'address'=> [
                'city' =>$customer->getAddressValue('shipping_address', 'city'),
                'country' => $customer->getAddressValue('shipping_address', 'country'),
                'line1' => $customer->getAddressValue('shipping_address', 'address1'),
                'line2' => $customer->getAddressValue('shipping_address', 'address2'),
                'postal_code' => $customer->getAddressValue('shipping_address', 'postal_code'),
                'state' => $customer->getAddressValue('shipping__address', 'state_province')
                ]
            ];

            $return['shipping_details'] = $shippingDetails;
        }

        echo json_encode($return);

        exit();

    }

    public function isExternal()
    {
        return false;
    }
}

return __NAMESPACE__;
