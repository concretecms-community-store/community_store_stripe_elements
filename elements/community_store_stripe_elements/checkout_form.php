<?php defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
?>
<input type="hidden" value="" name="stripeToken" id="stripeToken"/>

<div class="form-group">
    <div id="card-element" class="form-control">
        <!-- A Stripe Element will be inserted here. -->
    </div>
</div>
<p id="card-errors" role="alert" class="text-danger"></p>
<br />

<script>

    $(window).on('load', function () {
        var stripe = null
        var elements = null
        var card = null
        var clientSecret = '<?php echo $publicElementsAPIKey; ?>'
        var button = $('#card-element').closest('.store-payment-method-container').find('.store-btn-complete-order')

        let callback = function () {
            let style = {
                base: {
                    fontSmoothing: 'antialiased',
                    fontSize: '16px'
                }
            }

            stripe = Stripe(clientSecret)

            elements = stripe.elements()
            card = elements.create('card', {style: style})
            card.mount('#card-element')

            // initially disable button, as will renable when card details entered and are valid

            button.prop('disabled', true)
            button.data('previous-label', button.val())

            card.on('change', function (event) {

                var displayError = document.getElementById('card-errors')

                if (event.complete) {
                    button.prop('disabled', false)
                    button.val(button.data('previous-label'))
                } else {
                    button.prop('disabled', true)
                }

                if (event.error) {
                    displayError.textContent = event.error.message
                    button.val(button.data('previous-label'))
                } else {
                    displayError.textContent = ''
                }

            });
        }

        let URL = 'js.stripe.com/v3/'
        let documentTag = document, tag = 'script'
            object = documentTag.createElement(tag),
            scriptTag = documentTag.getElementsByTagName(tag)[0]
        object.src = '//' + URL
        if (callback) {
            object.addEventListener('load', function (e) {
                callback(null, e)
            }, false)
        }
        scriptTag.parentNode.insertBefore(object, scriptTag)

        $('.store-btn-complete-order').on('click', function (e) {

            // Open Checkout with further options
            var currentpmid = $('input[name="payment-method"]:checked:first').data('payment-method-id');

            if (currentpmid === <?= $pmID; ?>) {

                $(this).prop('disabled', true)

                $(this).val('<?= t('Processing...'); ?>')

                $.ajax({
                    url: '<?= \URL::to('/checkout/stripeelementscreatesession'); ?>',
                    type: 'get',
                    cache: false,
                    dataType: 'json',
                    success: function (data) {

                        let form = $('#store-checkout-form-group-payment');
                        let paymentData = {
                            payment_method: {
                                card: card,
                                billing_details: {
                                    address: {
                                        city: data.billing_details.city,
                                        country: data.billing_details.country,
                                        line1: data.billing_details.line1,
                                        line2: data.billing_details.line2,
                                        postal_code: data.billing_details.postal_code,
                                        state: data.billing_details.state
                                    },
                                    email: data.email,
                                    name: data.name,
                                    phone: data.phone
                                },
                            }
                        }

                        if (data.shipping_details) {

                            paymentData.shipping = {
                                name: data.shipping_details.name,
                                address: {
                                    city: data.shipping_details.address.city,
                                    country: data.shipping_details.address.country,
                                    line1: data.shipping_details.address.line1,
                                    line2: data.shipping_details.address.line2,
                                    postal_code: data.shipping_details.address.postal_code,
                                    state: data.shipping_details.address.state
                                }
                            }
                        }


                        stripe
                            .confirmCardPayment(data.client_secret,
                                paymentData
                            )
                            .then(function (result) {
                                if (result.error) {
                                    var errorElement = document.getElementById('card-errors')
                                    errorElement.textContent = result.error.message
                                    button.prop('disabled', false)
                                    button.val(button.data('previous-label'))
                                } else {
                                    // The payment succeeded
                                    var payment_intent_id = result.paymentIntent.id

                                    $('#stripeToken').val(payment_intent_id)
                                    form.submit()
                                }
                            });

                    }
                });

                e.preventDefault()
            }
        });

    });
</script>
