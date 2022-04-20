<?php
namespace Concrete\Package\CommunityStoreStripeElements\Src\CommunityStore\Payment\Methods\CommunityStoreStripeElements;

use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
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
        $this->set('stripeElementsSigningSecretKey', Config::get('community_store_stripe_elements.signingSecretKey'));
        $this->set('stripeElementsTestSigningSecretKey', Config::get('community_store_stripe_elements.testSigningSecretKey'));
        $this->set('stripeElementsMinimum', Config::get('community_store_stripe_elements.minimum'));
        $this->set('stripeElementsMaximum', Config::get('community_store_stripe_elements.maximum'));
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
        Config::save('community_store_stripe_elements.minimum', $data['stripeElementsMinimum']);
        Config::save('community_store_stripe_elements.maximum', $data['stripeElementsMaximum']);
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

            $orderID = Session::get('stripeOrderID');

            if ($orderID) {
                $order = Order::getByID($orderID);

                if ($order && !$order->getTransactionReference()) {
                    $order->completeOrder($stripeToken, true);
                    $order->updateStatus(StoreOrderStatus::getStartingStatus()->getHandle());
                    Session::remove('stripeOrderID');
                    echo json_encode(['error'=>0, 'transactionReference'=> $stripeToken]);
                    exit();
                }
            }
        }

        echo json_encode(['error'=>1, 'errorMessage'=>t('Invalid Transaction'), 'transactionReference'=> false]);
        exit();

    }

    public function getPaymentMinimum()
    {
        $defaultMin = 0.5;

        $minconfig = trim(Config::get('community_store_stripe_elements.minimum'));

        if ('' == $minconfig) {
            return $defaultMin;
        } else {
            return max($minconfig, $defaultMin);
        }
    }

    public function getPaymentMaximum()
    {
        $defaultMax = 1000000000;

        $maxconfig = trim(Config::get('community_store_stripe_elements.maximum'));
        if ('' == $maxconfig) {
            return $defaultMax;
        } else {
            return min($maxconfig, $defaultMax);
        }
    }


    public function getName()
    {
        return 'Stripe Elements';
    }

    public function createSession()
    {
        $existing = Session::get('stripeOrderID');

        if ($existing) {
            $existingOrder = Order::getByID($existing);

            if ($existingOrder && $existingOrder->getExternalPaymentRequested()) {
                $existingOrder->remove();
            }
        }

        $pm = StorePaymentMethod::getByHandle('community_store_stripe_elements');
        $order = Order::add($pm, null, 'incomplete');

        Session::set('stripeOrderID', $order->getOrderID());

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
            'metadata' => [t('Phone')=> $customer->getValue('billing_phone'), 'Order' => $order->getOrderID()]
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


    function chargeResponse() {

        $mode = Config::get('community_store_stripe_elements.mode');

        if ($mode == 'live') {
            $secretKey = Config::get('community_store_stripe_elements.livePrivateApiKey');
            $signingSecretKey = Config::get('community_store_stripe_elements.signingSecretKey');
        } else {
            $secretKey = Config::get('community_store_stripe_elements.testPrivateApiKey');
            $signingSecretKey = Config::get('community_store_stripe_elements.testSigningSecretKey');
        }


        if ($secretKey && $signingSecretKey) {
            \Stripe\Stripe::setApiKey($secretKey);

            $payload = @file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            $event = null;

            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $signingSecretKey
                );
            } catch (\UnexpectedValueException $e) {
                // Invalid payload
                http_response_code(400);
                exit();
            } catch (\Stripe\Error\SignatureVerification $e) {
                // Invalid signature
                http_response_code(400);
                exit();
            }

            $success = false;

            // Handle the checkout.session.completed event
            if ($event->type == 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;
                $order = StoreOrder::getByID($paymentIntent->metadata->Order);

                if ($order && !$order->getTransactionReference()) {
                    if ($order->getExternalPaymentRequested()) {
                        $order->completeOrder($paymentIntent->id);
                        $order->updateStatus(StoreOrderStatus::getStartingStatus()->getHandle());
                        $success = true;
                    }
                }
            }

            // handle a refund
            if ($event->type == 'charge.refunded') {
                $session = $event->data->object;

                $em = \Concrete\Core\Support\Facade\DatabaseORM::entityManager();
                $order = $em->getRepository('\Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order')->findOneBy(['transactionReference' => $session->payment_intent]);

                if ($order) {
                    $order->setRefunded(new \DateTime());
                    $order->setRefundReason($session->refunds->data->reason);
                    $order->save();
                    $success = true;
                }
            }

            if ($success) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(400);
        }
    }

    public function isExternal()
    {
        return true;
    }
}

return __NAMESPACE__;
