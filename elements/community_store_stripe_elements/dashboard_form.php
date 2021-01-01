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
