<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="form-group">
    <?=$form->label('stripeElementsCurrency', t('Currency'))?>
    <?=$form->select('stripeElementsCurrency', $stripeElementsCurrencies, $stripeElementsCurrency)?>
</div>

<div class="form-group">
    <?=$form->label('stripeElementsMode', t('Mode'))?>
    <?=$form->select('stripeElementsMode', ['test' => t('Test'), 'live' => t('Live')], $stripeElementsMode)?>
</div>

<div class="form-group">
    <?=$form->label('stripeElementsTestPublicApiKey', t('Test Publishable Key'))?>
    <input type="text" name="stripeElementsTestPublicApiKey" value="<?=$stripeElementsTestPublicApiKey?>" class="form-control">
</div>

<div class="form-group">
    <?=$form->label('stripeElementsTestPrivateApiKey', t('Test Secret Key'))?>
    <input type="text" name="stripeElementsTestPrivateApiKey" value="<?=$stripeElementsTestPrivateApiKey?>" class="form-control">
</div>

<div class="form-group">
    <?=$form->label('stripeElementsLivePublicApiKey', t('Live Publishable Key'))?>
    <input type="text" name="stripeElementsLivePublicApiKey" value="<?=$stripeElementsLivePublicApiKey?>" class="form-control">
</div>

<div class="form-group">
    <?=$form->label('stripeElementsLivePrivateApiKey', t('Live Secret Key'))?>
    <input type="text" name="stripeElementsLivePrivateApiKey" value="<?=$stripeElementsLivePrivateApiKey?>" class="form-control">
</div>


<?=$form->label('webhook', t('Required Webhook'))?>
<p><?= t('Within the Stripe Dashboard configure a Webhook endpoint for the following URL'); ?>:
    <br /><a href="<?= \URL::to('/checkout/stripeelementsresponse'); ?>"><?= \URL::to('/checkout/stripeelementsresponse'); ?></a></p>
<p><?= t('With the Events to send'); ?>:
    <span class="label label-primary">payment_intent.succeeded</span>
    <span class="label label-primary">charge.refunded</span>
</p>

<p><?= t('After creating the webhook, the Signing Secret can be found within webhook details page'); ?></p>

<div class="form-group">
    <?=$form->label('stripeElementsTestSigningSecretKey', t('Test Signing Secret Key'))?>
    <input type="text" name="stripeElementsTestSigningSecretKey" value="<?=$stripeElementsTestSigningSecretKey?>" class="form-control">
</div>


<div class="form-group">
    <?=$form->label('stripeElementsSigningSecretKey', t('Live Signing Secret Key'))?>
    <input type="text" name="stripeElementsSigningSecretKey" value="<?=$stripeElementsSigningSecretKey?>" class="form-control">
</div>
