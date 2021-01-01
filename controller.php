<?php

namespace Concrete\Package\CommunityStoreStripeElements;

use \Concrete\Core\Package\Package;
use \Concrete\Core\Support\Facade\Route;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class Controller extends Package
{
    protected $pkgHandle = 'community_store_stripe_elements';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '0.9';
    protected $packageDependencies = ['community_store'=>'2.0'];

    public function on_start()
    {
        require __DIR__ . '/vendor/autoload.php';
        Route::register('/checkout/stripeelementscreatesession','\Concrete\Package\CommunityStoreStripeElements\Src\CommunityStore\Payment\Methods\CommunityStoreStripeElements\CommunityStoreStripeElementsPaymentMethod::createSession');
    }

    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStoreStripeElements\Src\CommunityStore',
    ];

    public function getPackageDescription()
    {
        return t("Stripe Elements Payment Method for Community Store");
    }

    public function getPackageName()
    {
        return t("Stripe Elements Payment Method");
    }

    public function install()
    {
        $pkg = parent::install();
        $pm = new PaymentMethod();
        $pm->add('community_store_stripe_elements','Stripe Elements',$pkg);
    }
    public function uninstall()
    {
        $pm = PaymentMethod::getByHandle('community_store_stripe_elements');
        if ($pm) {
            $pm->delete();
        }
        $pkg = parent::uninstall();
    }

}
?>