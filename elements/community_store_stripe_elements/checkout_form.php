<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
?>
<input type="hidden" value="" name="stripeToken" id="stripeToken"/>

<div class="form-group">
    <div id="payment-element">
        <!-- A Stripe Element will be inserted here. -->
    </div>
</div>
<p id="card-errors" role="alert" class="text-danger"></p>
<br />

<script>
    var checkoutFormGroupPayment = document.getElementById('store-checkout-form-group-payment')

    if (!checkoutFormGroupPayment.classList.contains('stripe-elements-init')) {
        window.addEventListener('load', function () {

            var paymentButton = $('#payment-element').closest('.store-payment-method-container').find('.store-btn-complete-order');
            paymentButton.prop('disabled', true);

            var $div = $("#store-checkout-form-group-payment");
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    var attributeValue = $(mutation.target).prop(mutation.attributeName);
                    //alert("Class attribute changed to:", attributeValue);
                });
            });

            observer.observe($div[0], {
                attributes: true,
                attributeFilter: ['class']
            });

            var stripe = null
            var elements = null
            var cardElement = null
            var publicKey = '<?php echo $publicElementsAPIKey; ?>'
            let paymentData = {};
            let clientSecret = false;

            $.ajax({
                url: '<?= \URL::to('/checkout/stripeelementscreatesession'); ?>',
                type: 'get',
                cache: false,
                dataType: 'json',
                success: function (data) {

                    clientSecret = data.client_secret;

                    let form = $('#store-checkout-form-group-payment');
                    paymentData = {
                        payment_method: {

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

                    stripe = Stripe(publicKey)

                    elements = stripe.elements({clientSecret: clientSecret})
                    cardElement = elements.create('payment')
                    cardElement.mount('#payment-element')

                    // initially disable button, as will re-enable when card details entered and are valid
                    paymentButton.prop('disabled', true)
                    paymentButton.data('previous-label', paymentButton.val())

                    cardElement.on('change', function (event) {

                        var displayError = document.getElementById('card-errors')

                        if (event.complete) {
                            paymentButton.prop('disabled', false)
                            paymentButton.val(paymentButton.data('previous-label'))
                        } else {
                            paymentButton.prop('disabled', true)
                        }

                        if (event.error) {
                            displayError.textContent = event.error.message
                            paymentButton.val(paymentButton.data('previous-label'))
                        } else {
                            displayError.textContent = ''
                        }

                    });
                }
            });

            $("[data-payment-method-id='<?= $pmID; ?>'] .store-btn-complete-order").on('click', function (e) {

                $(this).prop('disabled', true)

                $(this).val('<?= t('Processing...'); ?>')

                stripe
                    .confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: "<?= $returnUrl; ?>",
                        },
                    })
                    .then(function (result) {
                        if (result.error) {
                            var errorElement = document.getElementById('card-errors')
                            errorElement.textContent = result.error.message
                            paymentButton.prop('disabled', false)
                            paymentButton.val(paymentButton.data('previous-label'))
                        }
                    });

                e.preventDefault()
            });

        });
        checkoutFormGroupPayment.classList.add('stripe-elements-init');
    }

</script>
