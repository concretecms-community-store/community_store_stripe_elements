<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>

<div class="form-group">
    <?= $form->label('invoiceMinimum', t('Minimum Order Value'))?>
    <?= $form->number("stripeElementsMinimum", $stripeElementsMinimum); ?>
</div>

<div class="form-group">
    <?= $form->label('invoiceMaximum', t('Maximum Order Value'))?>
    <?= $form->number("stripeElementsMaximum", $stripeElementsMaximum); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeElementsCurrency', t('Currency'))?>
    <?= $form->select('stripeElementsCurrency', $stripeElementsCurrencies, $stripeElementsCurrency)?>
</div>

<div class="form-group">
    <?= $form->label('stripeElementsMode', t('Mode'))?>
    <?= $form->select('stripeElementsMode', ['test' => t('Test'), 'live' => t('Live')], $stripeElementsMode)?>
</div>

<div class="form-group">
    <?= $form->label('stripeElementsTestPublicApiKey', t('Test Publishable Key'))?>
    <?= $form->text("stripeElementsTestPublicApiKey", $stripeElementsTestPublicApiKey); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeElementsTestPrivateApiKey', t('Test Secret Key'))?>
    <?= $form->text("stripeElementsTestPrivateApiKey", $stripeElementsTestPrivateApiKey); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeElementsLivePublicApiKey', t('Live Publishable Key'))?>
    <?= $form->text("stripeElementsLivePublicApiKey", $stripeElementsLivePublicApiKey); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeElementsLivePrivateApiKey', t('Live Secret Key'))?>
    <?= $form->text("stripeElementsLivePrivateApiKey", $stripeElementsLivePrivateApiKey); ?>
</div>


<?= $form->label('webhook', t('Required Webhook'))?>
<p><?= t('Within the Stripe Dashboard configure a Webhook endpoint for the following URL'); ?>:
    <br /><a href="<?= \URL::to('/checkout/stripeelementsresponse'); ?>"><?= \URL::to('/checkout/stripeelementsresponse'); ?></a></p>
<p><?= t('With the Events to send'); ?>:
    <span class="label label-primary">payment_intent.succeeded</span>
    <span class="label label-primary">charge.refunded</span>
</p>

<p><?= t('After creating the webhook, the Signing Secret can be found within webhook details page'); ?></p>

<div class="form-group">
    <?= $form->label('stripeElementsTestSigningSecretKey', t('Test Signing Secret Key'))?>
    <?= $form->text("stripeElementsTestSigningSecretKey", $stripeElementsTestSigningSecretKey); ?>
</div>


<div class="form-group">
    <?= $form->label('stripeElementsSigningSecretKey', t('Live Signing Secret Key'))?>
    <?= $form->text("stripeElementsSigningSecretKey", $stripeElementsSigningSecretKey); ?>
</div>
