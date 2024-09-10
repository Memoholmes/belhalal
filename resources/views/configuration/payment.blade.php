<!-- Page Heading -->
<h3><?= __tr('Payment Gateways') ?></h3>
<!-- /Page Heading -->
<hr>
<?php $isExtendedLicence = (getStoreSettings('product_registration', 'licence') === 'dee257a8c3a2656b7d7fbe9a91dd8c7c41d90dc9'); ?>
<!-- Payment Setting Form -->
@if(!$isExtendedLicence)
<div class="alert alert-warning my-3">
	<strong title="Extended Licence Required"><?= __tr('Extended Licence Required') ?></strong> <br>
	<?= __tr('To use the payment gateway to charge customers you need to buy an Extended licence. While you can test it with Regular licence, But you need to purchase Extended licence to use Live Keys') ?>
</div>
@endif
<form class="lw-ajax-form lw-form" method="post" data-callback="onPaymentGatewayFormCallback" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<!-- input field body -->
	<div class="form-group mt-2">
		<!-- paypal settings -->
		<fieldset class="lw-fieldset mb-3">
			<legend class="lw-fieldset-legend"><img src="<?= asset('imgs/payment-images/paypal-small.png') ?>" alt="<?= __tr('PayPal') ?>"></legend>

			<!-- Enable Paypal Checkout field -->
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="hidden" name="enable_paypal" value="0">
				<input type="checkbox" class="custom-control-input" id="lwEnablePaypal" name="enable_paypal" <?= $configurationData['enable_paypal'] == true ? 'checked' : '' ?> value="true">
				<label class="custom-control-label" for="lwEnablePaypal"><?= __tr('Enable Paypal Checkout')  ?></label>
			</div>
			<!-- / Enable Paypal Checkout field -->

			<span id="lwPaypalCheckoutContainer">
				<!-- use testing paypal checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use testing input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUsePaypalCheckoutTest" name="use_test_paypal_checkout" class="custom-control-input" value="1" <?= $configurationData['use_test_paypal_checkout'] == true ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUsePaypalCheckoutTest"><?= __tr('Use Testing') ?></label>
						</div>
					</legend>
					<!-- /use testing input radio field -->

					<!-- show after added testing paypal checkout information -->
					<div class="btn-group" id="lwTestPaypalCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Testing Paypal Checkout keys are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateTestPaypalCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added testing paypal checkout information -->

					<!-- paypal test key exists hidden field -->
					<input type="hidden" name="paypal_test_keys_exist" id="lwPaypalTestKeysExist" value="<?= $configurationData['paypal_checkout_testing_client_id'] ?>" />
					<!-- paypal test key exists hidden field -->

					<div id="lwTestPaypalInputField">
						<!-- Testing Client Id Key -->
						<div class="mb-3">
							<label for="lwPaypalTestClientId"><?= __tr('Client Id') ?></label>
							<input type="text" class="form-control form-control-user" name="paypal_checkout_testing_client_id" id="lwPaypalTestClientId" placeholder="<?= __tr('Client Id') ?>">
						</div>
						<!-- / Testing Client Id Key -->

						<!-- Testing Secret Key -->
						<div class="mb-3">
							<label for="lwPaypalTestSecretKey"><?= __tr('Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" name="paypal_checkout_testing_secret_key" id="lwPaypalTestSecretKey" placeholder="<?= __tr('Secret Key') ?>">
						</div>
						<!-- / Testing Secret Key -->
					</div>
				</fieldset>
				<!-- /use testing paypal checkout input fieldset -->

				<!-- use live paypal checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use live input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUsePaypalCheckoutLive" name="use_test_paypal_checkout" class="custom-control-input" value="0" <?= $configurationData['use_test_paypal_checkout'] == false ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUsePaypalCheckoutLive"><?= __tr('Use Live') ?></label>
						</div>
					</legend>
					<!-- /use live input radio field -->

					@if($isExtendedLicence)
					<!-- show after added Live paypal checkout information -->
					<div class="btn-group" id="lwLivePaypalCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Live Paypal Checkout keys are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateLivePaypalCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added Live paypal checkout information -->

					<!-- paypal live key exists hidden field -->
					<input type="hidden" name="paypal_live_keys_exist" id="lwPaypalLiveKeysExist" value="<?= $configurationData['paypal_checkout_live_client_id'] ?>" />
					<!-- paypal live key exists hidden field -->

					<div id="lwLivePaypalInputField">
						<!-- Live Client Id Key -->
						<div class="mb-3">
							<label for="lwPaypalLiveClientId"><?= __tr('Client Id') ?></label>
							<input type="text" class="form-control form-control-user" name="paypal_checkout_live_client_id" id="lwPaypalLiveClientId" placeholder="<?= __tr('Client Id') ?>">
						</div>
						<!-- / Live Client Id Key -->

						<!-- Live Secret Key -->
						<div class="mb-3">
							<label for="lwPaypalLiveSecretKey"><?= __tr('Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" name="paypal_checkout_live_secret_key" id="lwPaypalLiveSecretKey" placeholder="<?= __tr('Secret Key') ?>">
						</div>
						<!-- / Live Secret Key -->
					</div>
					@else
					<div class="alert alert-danger">
						{{  __tr('Extended licence required to use live keys') }}
					</div>
					@endif
				</fieldset>
				<!-- /use live paypal checkout input fieldset -->
			</span>
		</fieldset>
		<!-- / paypal settings -->

		<!-- stripe settings -->
		<fieldset class="lw-fieldset mb-3">
			<legend class="lw-fieldset-legend"><img src="<?= asset('imgs/payment-images/stripe-small.png') ?>" alt="<?= __tr('Stripe') ?>"></legend>
			<!-- Enable stripe Checkout field -->
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="hidden" name="enable_stripe" value="0">
				<input type="checkbox" class="custom-control-input" id="lwEnableStripe" name="enable_stripe" <?= $configurationData['enable_stripe'] == true ? 'checked' : '' ?>>
				<label class="custom-control-label" for="lwEnableStripe"><?= __tr('Enable Stripe Checkout')  ?></label>
			</div>
			<!-- / Enable stripe Checkout field -->

            <div class="form-group mt-3">
                <label for="lwStripeWebhookUrl">{{ __tr('Stripe Webhook URL') }}</label>
			<div class="input-group  mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">{{  __tr('Webhook') }}</span>
                </div>
				<input type="text" class="form-control p-2" readonly id="lwStripeWebhookUrl" value="{{ route('stripe-webhook') }}">
				<div class="input-group-append">
					<button class="btn btn-outline-secondary" type="button" onclick="copyToClipboardWebhookUrl()">
						<i class="fas fa-solid fa-copy"></i>
					</button>
				</div>
			</div>

			<div class="text-danger help-text mt-2 text-sm"><p>{{  __tr('IMPORTANT: It is very important that you should add this Webhook to Stripe account, as all the payment information gets updated using this webhook. Go to the link given below and follow the steps') }}</p></div>
			<div class="alert alert-secondary">
				<a target="_blank" href="https://stripe.com/docs/webhooks/go-live">https://stripe.com/docs/webhooks/go-live</a>
			</div>
            </div>

			<span id="lwStripeCheckoutContainer">
				<!-- use testing stripe checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use testing input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUseStripeCheckoutTest" name="use_test_stripe" class="custom-control-input" value="1" <?= $configurationData['use_test_stripe'] == true ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUseStripeCheckoutTest"><?= __tr('Use Testing') ?></label>
						</div>
					</legend>
					<!-- /use testing input radio field -->

					<!-- show after added testing stipe checkout information -->
					<div class="btn-group" id="lwTestStripeCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Testing Stripe Checkout keys are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateTestStripeCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added testing stipe checkout information -->

					<!-- stripe test secret key exists hidden field -->
					<input type="hidden" name="stripe_test_keys_exist" id="lwStripeTestKeysExist" value="<?= $configurationData['stripe_testing_secret_key'] ?>" />
					<!-- stripe test secret key exists hidden field -->

					<div id="lwTestStripeInputField">
						<!-- Testing Secret Key Key -->
						<div class="mb-3">
							<label for="lwStripeTestSecretKey"><?= __tr('Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwStripeTestSecretKey" name="stripe_testing_secret_key" placeholder="<?= __tr('Secret Key') ?>">
						</div>
						<!-- / Testing Secret Key Key -->

						<!-- Testing Publish Key -->
						<div class="mb-3">
							<label for="lwStripeTestPublishKey"><?= __tr('Publish Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwStripeTestPublishKey" name="stripe_testing_publishable_key" placeholder="<?= __tr('Publish Key') ?>">
						</div>
						<!-- / Testing Publish Key -->
						<!-- Stripe Webhook Secret -->
                        <div class="mb-3">
                            <label for="lwStripeTestWebhookSecret">
                                <?= __tr('Stripe Webhook Secret') ?>
                            </label>
                            <input type="text" class="form-control form-control-user" value=""
                                id="lwStripeTestWebhookSecret" name="stripe_testing_webhook_secret"
                                placeholder="<?= __tr('Stripe Webhook Secret') ?>">
                        </div>
                        <!-- / Stripe Webhook Secret -->
					</div>
				</fieldset>
				<!-- /use testing paypal checkout input fieldset -->

				<!-- use live stripe checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use live input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUseStripeCheckoutLive" name="use_test_stripe" class="custom-control-input" value="0" <?= $configurationData['use_test_stripe'] == false ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUseStripeCheckoutLive"><?= __tr('Use Live') ?></label>
						</div>
					</legend>
					<!-- /use live input radio field -->
					@if($isExtendedLicence)
					<!-- show after added Live stripe checkout information -->
					<div class="btn-group" id="lwLiveStripeCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Live Stripe Checkout keys are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateLiveStripeCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added Live stripe checkout information -->

					<!-- stripe live secret key exists hidden field -->
					<input type="hidden" name="stripe_live_keys_exist" id="lwStripeLiveKeysExist" value="<?= $configurationData['stripe_live_secret_key'] ?>" />
					<!-- stripe live secret key exists hidden field -->

					<div id="lwLiveStripeInputField">
						<!-- Live Secret Key Key -->
						<div class="mb-3">
							<label for="lwStripeLiveSecretKey"><?= __tr('Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwStripeLiveSecretKey" name="stripe_live_secret_key" placeholder="<?= __tr('Secret Key') ?>">
						</div>
						<!-- / Live Secret Key Key -->

						<!-- Live Publish Key -->
						<div class="mb-3">
							<label for="lwStripeLivePublishKey"><?= __tr('Publish Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwStripeLivePublishKey" name="stripe_live_publishable_key" placeholder="<?= __tr('Publish Key') ?>">
						</div>
						<!-- / Live Publish Key -->
						<!-- Live Stripe Webhook Secret -->
                        <div class="mb-3">
                            <label for="lwStripeLiveWebhookSecret">
                                <?= __tr('Stripe Webhook Secret') ?>
                            </label>
                            <input type="text" class="form-control form-control-user" value=""
                                id="lwStripeLiveWebhookSecret" name="stripe_live_webhook_secret"
                                placeholder="<?= __tr('Stripe Webhook Secret') ?>">
                        </div>
                        <!-- / Live Stripe Webhook Secret -->
					</div>
					@else
					<div class="alert alert-danger">
						Extended licence required to use live keys
					</div>
					@endif
				</fieldset>
				<!-- /use live stripe checkout input fieldset -->
			</span>
		</fieldset>
		<!-- / stripe settings -->

		<!-- razorpay settings -->
		<fieldset class="lw-fieldset mb-3">
			<legend class="lw-fieldset-legend"><img src="<?= asset('imgs/payment-images/razorpay-small.png') ?>" alt="<?= __tr('Razorpay') ?>"></legend>
			<!-- Enable razorpay Checkout field -->
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="hidden" name="enable_razorpay" value="0">
				<input type="checkbox" class="custom-control-input" id="lwEnableRazorpay" name="enable_razorpay" <?= $configurationData['enable_razorpay'] == true ? 'checked' : '' ?>>
				<label class="custom-control-label" for="lwEnableRazorpay"><?= __tr('Enable Razorpay Checkout')  ?></label>
			</div>
			<!-- / Enable razorpay Checkout field -->
            <div class="form-group mt-3">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">{{  __tr('Webhook') }}</span>
                    </div>
                    <input type="text" class="form-control" readonly id="lwRazorPayWebhookUrl" value="{{ route('razorpay-webhook') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboardRazorpayWebhookUrl()">
                            <i class="fas fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
			<div class="text-danger help-text mt-2 text-sm"><p>
				{{  __tr('IMPORTANT: It is very important that you should add this Webhook to Razorpay account, as all the payment information gets updated using this webhook. Go to the link given below and follow the steps') }}</p></div>
			<div class="alert alert-secondary">
				<a target="_blank" href="https://razorpay.com/docs/webhooks/setup-edit-payments/">https://razorpay.com/docs/webhooks/setup-edit-payments/</a>
			</div>

			<span id="lwRazorpayCheckoutContainer">
				<!-- use testing razorpay checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use testing input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUseRazorpayCheckoutTest" name="use_test_razorpay" class="custom-control-input" value="1" <?= $configurationData['use_test_razorpay'] == true ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUseRazorpayCheckoutTest"><?= __tr('Use Testing') ?></label>
						</div>
					</legend>
					<!-- /use testing input radio field -->

					<!-- show after added testing razorpay checkout information -->
					<div class="btn-group" id="lwTestRazorpayCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Testing Razorpay Checkout keys are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateTestRazorpayCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added testing razorpay checkout information -->

					<!-- razorpay test secret key exists hidden field -->
					<input type="hidden" name="razorpay_test_keys_exist" id="lwRazorpayTestKeysExist" value="<?= $configurationData['razorpay_testing_key'] ?>" />
					<!-- razorpay test secret key exists hidden field -->

					<div id="lwTestRazorpayInputField">
						<!-- Testing Razorpay Key -->
						<div class="mb-3">
							<label for="lwRazorpayTestKey"><?= __tr('Razorpay Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwRazorpayTestKey" name="razorpay_testing_key" placeholder="<?= __tr('Razorpay Key') ?>">
						</div>
						<!-- / Testing Razorpay Key -->

						<!-- Testing Razorpay Secret Key -->
						<div class="mb-3">
							<label for="lwRazorpayTestSecretKey"><?= __tr('Razorpay Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwRazorpayTestSecretKey" name="razorpay_testing_secret_key" placeholder="<?= __tr('Razorpay Secret Key') ?>">
						</div>
						<!-- / Testing Razorpay Secret Key -->

						 <!-- Webhook secret -->
						 <div class="mb-3">
                            <label for="lwRazorPayTestWebhookSecretKey"><?= __tr('Webhook Signing Secret') ?></label>
                            <input type="text" class="form-control form-control-user" value="" id="lwRazorPayTestWebhookSecretKey" name="razorpay_testing_webhook_secret"
                                placeholder="<?= __tr('Webhook Signing Secret') ?>">
                        </div>
                    <!-- / Webhook secret -->
					</div>
				</fieldset>
				<!-- /use testing razorpay checkout input fieldset -->

				<!-- use live Razorpay checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use live input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUseRazorpayCheckoutLive" name="use_test_razorpay" class="custom-control-input" value="0" <?= $configurationData['use_test_razorpay'] == false ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUseRazorpayCheckoutLive"><?= __tr('Use Live') ?></label>
						</div>
					</legend>
					<!-- /use live input radio field -->
					@if($isExtendedLicence)
					<!-- show after added Live razorpay checkout information -->
					<div class="btn-group" id="lwLiveRazorpayCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Live Razorpay Checkout keys are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateLiveRazorpayCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added Live razorpay checkout information -->

					<!-- razorpay live secret key exists hidden field -->
					<input type="hidden" name="razorpay_live_keys_exist" id="lwRazorpayLiveKeysExist" value="<?= $configurationData['razorpay_live_key'] ?>" />
					<!-- razorpay live secret key exists hidden field -->

					<div id="lwLiveRazorpayInputField">
						<!-- Live Razorpay Key Key -->
						<div class="mb-3">
							<label for="lwRazorpayLiveKey"><?= __tr('Razorpay Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwRazorpayLiveKey" name="razorpay_live_key" placeholder="<?= __tr('Razorpay Key') ?>">
						</div>
						<!-- / Live Razorpay Key Key -->

						<!-- Live Secret Key -->
						<div class="mb-3">
							<label for="lwRazorpayLiveSecretKey"><?= __tr('Razorpay Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwRazorpayLiveSecretKey" name="razorpay_live_secret_key" placeholder="<?= __tr('Razorpay Secret Key') ?>">
						</div>
						<!-- / Live Secret Key -->
						 <!-- Webhook secret -->
						 <div class="mb-3">
                            <label for="lwRazorPayLiveWebhookSecretKey"><?= __tr('Webhook Signing Secret') ?></label>
                            <input type="text" class="form-control form-control-user" value="" id="lwRazorPayLiveWebhookSecretKey" name="razorpay_live_webhook_secret"
                                placeholder="<?= __tr('Webhook Signing Secret') ?>">
                    </div>
                    <!-- / Webhook secret -->
					</div>
					@else
					<div class="alert alert-danger">
						Extended licence required to use live keys
					</div>
					@endif
				</fieldset>
				<!-- /use live stripe checkout input fieldset -->
			</span>
		</fieldset>
		<!-- / razorpay settings -->

		<!-- Coingate settings -->
		<fieldset class="lw-fieldset mb-3">
			<legend class="lw-fieldset-legend"><img src="<?= asset('imgs/payment-images/coingate-small.png') ?>" alt="<?= __tr('Coingate') ?>"></legend>

			<!-- Enable coingate Checkout field -->
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="hidden" name="enable_coingate" value="0">
				<input type="checkbox" class="custom-control-input" id="lwEnableCoingate" name="enable_coingate" <?= $configurationData['enable_coingate'] == true ? 'checked' : '' ?>>
				<label class="custom-control-label" for="lwEnableCoingate"><?= __tr('Enable Coingate Checkout')  ?></label>
			</div>
			<!-- / Enable coingate Checkout field -->

			<span id="lwCoingateCheckoutContainer">
				<!-- use testing coingate checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use testing input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUseCoingateCheckoutTest" name="use_test_coingate" class="custom-control-input" value="1" <?= $configurationData['use_test_coingate'] == true ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUseCoingateCheckoutTest"><?= __tr('Use Testing') ?></label>
						</div>
					</legend>
					<!-- /use testing input radio field -->

					<!-- show after added testing razorpay checkout information -->
					<div class="btn-group" id="lwTestCoingateCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Testing Coingate Checkout Token are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateTestCoingateCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added testing Coingate checkout information -->

					<!-- Coingate test secret key exists hidden field -->
					<input type="hidden" name="coingate_test_token_exist" id="lwCoingateTestTokenExist" value="<?= $configurationData['coingate_test_token'] ?>" />
					<!-- Coingate test secret key exists hidden field -->

					<div id="lwTestCoingateInputField">
						<!-- Testing Coingate Secret Key -->
						<div class="mb-3">
							<label for="lwCoingateTestToken"><?= __tr('Coingate Test Token') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwCoingateTestToken" name="coingate_test_token" placeholder="<?= __tr('Coingate Test Token') ?>">
						</div>
						<!-- / Testing Coingate Secret Key -->
					</div>
				</fieldset>
				<!-- /use testing Coingate checkout input fieldset -->

				<!-- use live Coingate checkout input fieldset -->
				<fieldset class="lw-fieldset mb-3">
					<!-- use live input radio field -->
					<legend class="lw-fieldset-legend">
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" id="lwUseCoingateCheckoutLive" name="use_test_coingate" class="custom-control-input" value="0" <?= $configurationData['use_test_coingate'] == false ? 'checked' : '' ?>>
							<label class="custom-control-label" for="lwUseCoingateCheckoutLive"><?= __tr('Use Live') ?></label>
						</div>
					</legend>
					<!-- /use live input radio field -->
					@if($isExtendedLicence)
					<!-- show after added Live Coingate checkout information -->
					<div class="btn-group" id="lwLiveCoingateCheckoutExists">
						<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Live Coingate Checkout Token are installed.') ?></button>
						<button type="button" class="btn btn-light lw-btn" id="lwUpdateLiveCoingateCheckout"><?= __tr('Update') ?></button>
					</div>
					<!-- show after added Live Coingate checkout information -->

					<!-- Coingate live secret key exists hidden field -->
					<input type="hidden" name="coingate_live_token_exist" id="lwCoingateLiveKeysExist" value="<?= $configurationData['coingate_live_token'] ?>" />
					<!-- Coingate live secret key exists hidden field -->

					<div id="lwLiveCoingateInputField">
						<!-- Live Coingate Token -->
						<div class="mb-3">
							<label for="lwCoingateLiveToken"><?= __tr('Coingate Live Token') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwCoingateLiveToken" name="coingate_live_token" placeholder="<?= __tr('Coingate Live Token') ?>">
						</div>
						<!-- / Live Coingate Token -->
					</div>
					@else
					<div class="alert alert-danger">
						Extended licence required to use live keys
					</div>
					@endif
				</fieldset>
				<!-- /use live stripe checkout input fieldset -->
			</span>
		</fieldset>
		<!-- / Coingate settings -->
		{{-- CRYPTO  keys Settings start --}}
			
			<fieldset class="lw-fieldset mb-3">
				<legend class="lw-fieldset-legend"><img src="<?= asset('imgs/payment-images/crypto.png') ?>" alt="<?= __tr('CRYPTO') ?>"></legend>
	
				<!-- Enable crypto Checkout field -->
				<div class="custom-control custom-checkbox custom-control-inline">
					<input type="hidden" name="enable_crypto" value="0">
					<input type="checkbox" class="custom-control-input" id="lwEnableCrypto" name="enable_crypto" <?= $configurationData['enable_crypto'] == true ? 'checked' : '' ?>>
					<label class="custom-control-label" for="lwEnableCrypto"><?= __tr('Enable Crypto Checkout')  ?></label>
				</div>
				<!-- / Enable crypto Checkout field -->
				{{-- crypto webhook url --}}
				<div class="form-group mt-3">
					
				<div class="input-group  mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" id="basic-addon1">{{  __tr('Webhook') }}</span>
					</div>
					<input type="text" class="form-control p-2" readonly id="lwCryptoWebhookUrl" value="{{ route('crypto-webhook') }}">
					<div class="input-group-append">
						<button class="btn btn-outline-secondary" type="button" onclick="copyToClipboardWebhookUrl()">
							<i class="fas fa-solid fa-copy"></i>
						</button>
					</div>
				</div>
				
	
				<div class="text-danger help-text mt-2 text-sm"><p>{{  __tr('IMPORTANT: It is very important that you should add this Webhook to Crypto account, as all the payment information gets updated using this webhook. Go to the link given below and follow the steps') }}</p></div>
				<div class="alert alert-secondary">
					<a target="_blank" href="https://pay-docs.crypto.com/#api-reference-webhooks-troubleshooting">https://pay-docs.crypto.com/#api-reference-webhooks</a>
				</div>
				</div>
				{{-- crypto webhook url --}}
	
				<span id="lwCryptoCheckoutContainer">
					<!-- use testing crypto checkout input fieldset -->
					<fieldset class="lw-fieldset mb-3">
						<!-- use testing input radio field -->
						<legend class="lw-fieldset-legend">
							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="lwUseCryptoCheckoutTest" name="use_test_crypto" class="custom-control-input" value="1" <?= $configurationData['use_test_crypto'] == true ? 'checked' : '' ?>>
								<label class="custom-control-label" for="lwUseCryptoCheckoutTest"><?= __tr('Use Testing') ?></label>
							</div>
						</legend>
						<!-- /use testing input radio field -->
	
						<!-- show after added testing crypto checkout information -->
						<div class="btn-group" id="lwTestCryptoCheckoutExists">
							<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Testing Crypto Checkout Token are installed.') ?></button>
							<button type="button" class="btn btn-light lw-btn" id="lwUpdateTestCryptoCheckout"><?= __tr('Update') ?></button>
						</div>
						<!-- show after added testing Crypto checkout information -->
	
						<!-- Crypto test secret key exists hidden field -->
						<input type="hidden" name="crypto_testing_publishable_key_exist" id="lwCryptoTestKeysExist" value="<?= $configurationData['crypto_testing_publishable_key'] ?>" />

						
						<!-- Crypto test secret key exists hidden field -->
	
						<div id="lwTestCryptoInputField">
							<!-- Testing Crypto Secret Key -->
							<div class="mb-3">
								<label for="lwCryptoTestToken"><?= __tr('Crypto Test Token') ?></label>
								<input type="text" class="form-control form-control-user" value="" id="lwCryptoTestToken" name="crypto_testing_publishable_key" placeholder="<?= __tr('Crypto Test Token') ?>">
							</div>
							<!-- / Testing Crypto Secret Key -->
							<!-- Testing Crypto Secret Key -->
						<div class="mb-3">
							<label for="lwCryptoTestSecretKey"><?= __tr('Crypto Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwCryptoTestSecretKey" name="crypto_testing_secret_key" placeholder="<?= __tr('Crypto Secret Key') ?>">
						</div>
						<!-- / Testing Crypto Secret Key -->
							<!-- crypto Webhook Secret -->
							<div class="mb-3">
								<label for="lwCryptoTestWebhookSecret">
									<?= __tr('Crypto Webhook Secret') ?>
								</label>
								<input type="text" class="form-control form-control-user" value=""
									id="lwCryptoTestWebhookSecret" name="crypto_testing_webhook_secret"
									placeholder="<?= __tr('Crypto Webhook Secret') ?>">
							</div>
							<!-- / crypto Webhook Secret -->
						</div>
					</fieldset>
					<!-- /use testing Crypto checkout input fieldset -->
	
					<!-- use live Crypto checkout input fieldset -->
					<fieldset class="lw-fieldset mb-3">
						<!-- use live input radio field -->
						<legend class="lw-fieldset-legend">
							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="lwUseCryptoCheckoutLive" name="use_test_crypto" class="custom-control-input" value="0" <?= $configurationData['use_test_crypto'] == false ? 'checked' : '' ?>>
								<label class="custom-control-label" for="lwUseCryptoCheckoutLive"><?= __tr('Use Live') ?></label>
							</div>
						</legend>
						<!-- /use live input radio field -->
						@if($isExtendedLicence)
						<!-- show after added Live crypto checkout information -->
						<div class="btn-group" id="lwLiveCryptoCheckoutExists">
							<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Live Crypto Checkout Token are installed.') ?></button>
							<button type="button" class="btn btn-light lw-btn" id="lwUpdateLiveCryptoCheckout"><?= __tr('Update') ?></button>
						</div>
						<!-- show after added Live crypto checkout information -->
	
						<!-- crypto live secret key exists hidden field -->
						<input type="hidden" name="crypto_live_publishable_key_exist" id="lwCryptoLiveKeysExist" value="<?= $configurationData['crypto_live_publishable_key'] ?>" />
						<!-- crypto live secret key exists hidden field -->
	
						<div id="lwLiveCryptoInputField">
							<!-- Live crypto Token -->
							<div class="mb-3">
								<label for="lwCryptoLiveToken"><?= __tr('Crypto Live Token') ?></label>
								<input type="text" class="form-control form-control-user" value="" id="lwCryptoLiveToken" name="crypto_live_publishable_key" placeholder="<?= __tr('Crypto Live Token') ?>">
							</div>
							<!-- / Live Coingate Token -->
							<!-- Testing Razorpay Secret Key -->
						<div class="mb-3">
							<label for="lwCryptoTestSecretKey"><?= __tr('Crypto Secret Key') ?></label>
							<input type="text" class="form-control form-control-user" value="" id="lwCryptoTestSecretKey" name="crypto_live_secret_key" placeholder="<?= __tr('Crypto Secret Key') ?>">
						</div>
						<!-- / Testing Razorpay Secret Key -->
							<!-- crypto Webhook Secret -->
							<div class="mb-3">
								<label for="lwCryptoTestWebhookSecret">
									<?= __tr('Crypto Webhook Secret') ?>
								</label>
								<input type="text" class="form-control form-control-user" value=""
									id="lwCryptoTestWebhookSecret" name="crypto_live_webhook_secret"
									placeholder="<?= __tr('Crypto Webhook Secret') ?>">
							</div>
							<!-- / crypto Webhook Secret -->
						</div>
						@else
						<div class="alert alert-danger">
							Extended licence required to use live keys
						</div>
						@endif
					</fieldset>
					<!-- /use live crypto checkout input fieldset -->
				</span>
			</fieldset>
			<!-- / crypto settings -->
		{{-- //CRYPTO SETTINGS --}}
	</div>
	<!-- / input field body -->

	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>
<!-- /Payment Setting Form -->

@lwPush('appScripts')
<script>
	/*********** Paypal Enable / Disable Checkout start here ***********/
	var isPaypalCheckoutEnable = $('#lwEnablePaypal').is(':checked'),
		isUsePaypalCheckoutTest = $("#lwUsePaypalCheckoutTest").is(':checked'),
		lwUsePaypalCheckoutLive = $("#lwUsePaypalCheckoutLive").is(':checked');
	//paypal checkout is enable then add disable content class
	if (!isPaypalCheckoutEnable) {
		$('#lwPaypalCheckoutContainer').addClass('lw-disabled-block-content');
	}
	//paypal checkout is enable/disabled on change 
	//then add/remove disable content class
	$("#lwEnablePaypal").on('change', function(event) {
		isPaypalCheckoutEnable = $(this).is(":checked");
		//check is enable false then add class
		if (!isPaypalCheckoutEnable) {
			$("#lwPaypalCheckoutContainer").addClass('lw-disabled-block-content');
			//else remove class
		} else {
			$("#lwPaypalCheckoutContainer").removeClass('lw-disabled-block-content');
		}
	});

	//check paypal test mode is true then disable paypal live input field	
	if (isUsePaypalCheckoutTest) {
		$('#lwUpdateLivePaypalCheckout').attr('disabled', true);
		$('#lwLivePaypalInputField').addClass('lw-disabled-block-content');
		//check paypal test mode is false then disable paypal test input field	
	} else if (lwUsePaypalCheckoutLive) {
		$('#lwUpdateTestPaypalCheckout').attr('disabled', true);
		$('#lwTestPaypalInputField').addClass('lw-disabled-block-content');
	}

	//check paypal test mode is true on change 
	//then disable paypal live input field	
	$("#lwUsePaypalCheckoutTest").on('change', function(event) {
		var isUsePaypalCheckoutTest = $(this).is(':checked');
		if (isUsePaypalCheckoutTest) {
			$('#lwUpdateLivePaypalCheckout').attr('disabled', true);
			$('#lwUpdateTestPaypalCheckout').attr('disabled', false);
			$('#lwTestPaypalInputField').removeClass('lw-disabled-block-content');
			$('#lwLivePaypalInputField').addClass('lw-disabled-block-content');
		}
	});

	//check paypal test mode is false on change 
	//then disable paypal test input field	
	$("#lwUsePaypalCheckoutLive").on('change', function(event) {
		var lwUsePaypalCheckoutLive = $(this).is(':checked');
		if (lwUsePaypalCheckoutLive) {
			$('#lwUpdateTestPaypalCheckout').attr('disabled', true);
			$('#lwUpdateLivePaypalCheckout').attr('disabled', false);
			$('#lwLivePaypalInputField').removeClass('lw-disabled-block-content');
			$('#lwTestPaypalInputField').addClass('lw-disabled-block-content');
		}
	});
	/*********** Paypal Enable / Disable Checkout end here ***********/

	/*********** Paypal Testing Keys setting start here ***********/
	var isTestPaypalKeysInstalled = "<?= $configurationData['paypal_checkout_testing_client_id'] ?>",
		lwTestPaypalInputField = $('#lwTestPaypalInputField'),
		lwTestPaypalCheckoutExists = $('#lwTestPaypalCheckoutExists');

	// Check if test paypal keys are installed
	if (isTestPaypalKeysInstalled) {
		lwTestPaypalInputField.hide();
	} else {
		lwTestPaypalCheckoutExists.hide();
	}
	// Update paypal checkout testing keys
	$('#lwUpdateTestPaypalCheckout').click(function() {
		$("#lwPaypalTestKeysExist").val(0);
		lwTestPaypalInputField.show();
		lwTestPaypalCheckoutExists.hide();
	});
	/*********** Paypal Testing Keys setting end here ***********/

	/*********** Paypal Live Keys setting start here ***********/
	var isLivePaypalKeysInstalled = "<?= $configurationData['paypal_checkout_live_client_id'] ?>",
		lwLivePaypalInputField = $('#lwLivePaypalInputField'),
		lwLivePaypalCheckoutExists = $('#lwLivePaypalCheckoutExists');

	// Check if test paypal keys are installed
	if (isLivePaypalKeysInstalled) {
		lwLivePaypalInputField.hide();
	} else {
		lwLivePaypalCheckoutExists.hide();
	}
	// Update paypal checkout testing keys
	$('#lwUpdateLivePaypalCheckout').click(function() {
		$("#lwPaypalLiveKeysExist").val(0);
		lwLivePaypalInputField.show();
		lwLivePaypalCheckoutExists.hide();
	});
	/*********** Paypal Live Keys setting end here ***********/

	/*********** Stripe Enable / Disable Checkout start here ***********/
	var isStripeCheckoutEnable = $('#lwEnableStripe').is(':checked'),
		isUseStripeCheckoutTest = $("#lwUseStripeCheckoutTest").is(':checked'),
		isUseStripeCheckoutLive = $("#lwUseStripeCheckoutLive").is(':checked');

	if (!isStripeCheckoutEnable) {
		$('#lwStripeCheckoutContainer').addClass('lw-disabled-block-content');
	}
	$("#lwEnableStripe").on('change', function(event) {
		isStripeCheckoutEnable = $(this).is(":checked");
		//check is enable false then add class
		if (!isStripeCheckoutEnable) {
			$("#lwStripeCheckoutContainer").addClass('lw-disabled-block-content');
			//else remove class
		} else {
			$("#lwStripeCheckoutContainer").removeClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is true then disable stripe live input field	
	if (isUseStripeCheckoutTest) {
		$('#lwUpdateLiveStripeCheckout').attr('disabled', true);
		$('#lwLiveStripeInputField').addClass('lw-disabled-block-content');
		//check stripe test mode is false then disable stripe test input field	
	} else if (isUseStripeCheckoutLive) {
		$('#lwUpdateTestStripeCheckout').attr('disabled', true);
		$('#lwTestStripeInputField').addClass('lw-disabled-block-content');
	}

	//check stripe test mode is true on change 
	//then disable stripe live input field	
	$("#lwUseStripeCheckoutTest").on('change', function(event) {
		var isUseStripeCheckoutTest = $(this).is(':checked');
		if (isUseStripeCheckoutTest) {
			$('#lwUpdateLiveStripeCheckout').attr('disabled', true);
			$('#lwUpdateTestStripeCheckout').attr('disabled', false);
			$('#lwTestStripeInputField').removeClass('lw-disabled-block-content');
			$('#lwLiveStripeInputField').addClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is false on change 
	//then disable stripe test input field	
	$("#lwUseStripeCheckoutLive").on('change', function(event) {
		var isUseStripeCheckoutLive = $(this).is(':checked');
		if (isUseStripeCheckoutLive) {
			$('#lwUpdateTestStripeCheckout').attr('disabled', true);
			$('#lwUpdateLiveStripeCheckout').attr('disabled', false);
			$('#lwLiveStripeInputField').removeClass('lw-disabled-block-content');
			$('#lwTestStripeInputField').addClass('lw-disabled-block-content');
		}
	});
	/*********** Stripe Enable / Disable Checkout end here ***********/

	/*********** Stripe Testing Keys setting start here ***********/
	var isTestStripeKeysInstalled = "<?= $configurationData['stripe_testing_publishable_key'] ?>",
		lwTestStripeInputField = $('#lwTestStripeInputField'),
		lwTestStripeCheckoutExists = $('#lwTestStripeCheckoutExists');

	// Check if test stripe keys are installed
	if (isTestStripeKeysInstalled) {
		lwTestStripeInputField.hide();
	} else {
		lwTestStripeCheckoutExists.hide();
	}
	// Update stripe checkout testing keys
	$('#lwUpdateTestStripeCheckout').click(function() {
		$("#lwStripeTestKeysExist").val(0);
		lwTestStripeInputField.show();
		lwTestStripeCheckoutExists.hide();
	});
	/*********** Stripe Testing Keys setting end here ***********/

	/*********** Stripe Live Keys setting start here ***********/
	var isLiveStripePublishKeysInstalled = "<?= $configurationData['stripe_live_publishable_key'] ?>",
		lwLiveStripeInputField = $('#lwLiveStripeInputField'),
		lwLiveStripeCheckoutExists = $('#lwLiveStripeCheckoutExists');

	// Check if test Stripe keys are installed
	if (isLiveStripePublishKeysInstalled) {
		lwLiveStripeInputField.hide();
	} else {
		lwLiveStripeCheckoutExists.hide();
	}
	// Update Stripe checkout testing keys
	$('#lwUpdateLiveStripeCheckout').click(function() {
		$("#lwStripeLiveKeysExist").val(0);
		lwLiveStripeInputField.show();
		lwLiveStripeCheckoutExists.hide();
	});
	/*********** Stripe Live Keys setting end here ***********/

	/*********** Coingate Enable / Disable Checkout start here ***********/
	var isCoingateCheckoutEnable = $('#lwEnableCoingate').is(':checked'),
		isUseCoingateCheckoutTest = $("#lwUseCoingateCheckoutTest").is(':checked'),
		isUseCoingateCheckoutLive = $("#lwUseCoingateCheckoutLive").is(':checked');

	if (!isCoingateCheckoutEnable) {
		$('#lwCoingateCheckoutContainer').addClass('lw-disabled-block-content');
	}
	$("#lwEnableCoingate").on('change', function(event) {
		isCoingateCheckoutEnable = $(this).is(":checked");
		//check is enable false then add class
		if (!isCoingateCheckoutEnable) {
			$("#lwCoingateCheckoutContainer").addClass('lw-disabled-block-content');
			//else remove class
		} else {
			$("#lwCoingateCheckoutContainer").removeClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is true then disable stripe live input field	
	if (isUseCoingateCheckoutTest) {
		$('#lwUpdateLiveCoingateCheckout').attr('disabled', true);
		$('#lwLiveCoingateInputField').addClass('lw-disabled-block-content');
		//check Coingate test mode is false then disable Coingate test input field	
	} else if (isUseCoingateCheckoutLive) {
		$('#lwUpdateTestCoingateCheckout').attr('disabled', true);
		$('#lwTestCoingateInputField').addClass('lw-disabled-block-content');
	}

	//check Coingate test mode is true on change 
	//then disable Coingate live input field	
	$("#lwUseCoingateCheckoutTest").on('change', function(event) {
		var isUseCoingateCheckoutTest = $(this).is(':checked');
		if (isUseCoingateCheckoutTest) {
			$('#lwUpdateLiveCoingateCheckout').attr('disabled', true);
			$('#lwUpdateTestCoingateCheckout').attr('disabled', false);
			$('#lwTestCoingateInputField').removeClass('lw-disabled-block-content');
			$('#lwLiveCoingateInputField').addClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is false on change 
	//then disable stripe test input field	
	$("#lwUseCoingateCheckoutLive").on('change', function(event) {
		var isUseCoingateCheckoutLive = $(this).is(':checked');
		if (isUseCoingateCheckoutLive) {
			$('#lwUpdateTestCoingateCheckout').attr('disabled', true);
			$('#lwUpdateLiveCoingateCheckout').attr('disabled', false);
			$('#lwLiveCoingateInputField').removeClass('lw-disabled-block-content');
			$('#lwTestCoingateInputField').addClass('lw-disabled-block-content');
		}
	});

	/*********** Coingate Testing Keys setting start here ***********/
	var isTestCoingateKeysInstalled = "<?= $configurationData['coingate_test_token'] ?>",
		lwTestCoingateInputField = $('#lwTestCoingateInputField'),
		lwTestCoingateCheckoutExists = $('#lwTestCoingateCheckoutExists');

	// Check if test stripe keys are installed
	if (isTestCoingateKeysInstalled) {
		lwTestCoingateInputField.hide();
	} else {
		lwTestCoingateCheckoutExists.hide();
	}
	// Update Coingate checkout testing keys
	$('#lwUpdateTestCoingateCheckout').click(function() {
		$("#lwCoingateTestKeysExist").val(0);
		lwTestCoingateInputField.show();
		lwTestCoingateCheckoutExists.hide();
	});
	/*********** Coingate Testing Keys setting end here ***********/

	/*********** Coingate Live Keys setting start here ***********/
	var isLiveCoingateSecretKeysInstalled = "<?= $configurationData['coingate_live_token'] ?>",
		lwLiveCoingateInputField = $('#lwLiveCoingateInputField'),
		lwLiveCoingateCheckoutExists = $('#lwLiveCoingateCheckoutExists');

	// Check if test Stripe keys are installed
	if (isLiveCoingateSecretKeysInstalled) {
		lwLiveCoingateInputField.hide();
	} else {
		lwLiveCoingateCheckoutExists.hide();
	}
	// Update Coingate checkout testing keys
	$('#lwUpdateLiveCoingateCheckout').click(function() {
		$("#lwCoingateLiveKeysExist").val(0);
		lwLiveCoingateInputField.show();
		lwLiveCoingateCheckoutExists.hide();
	});
	/*********** Coingate Live Keys setting end here ***********/

	/*********** Coingate Enable / Disable Checkout end here ***********/

	/*********** Crypto Enable / Disable Checkout start here ***********/
	var isCryptoCheckoutEnable = $('#lwEnableCrypto').is(':checked'),
		isUseCryptoCheckoutTest = $("#lwUseCryptoCheckoutTest").is(':checked'),
		isUseCryptoCheckoutLive = $("#lwUseCryptoCheckoutLive").is(':checked');

	if (!isCryptoCheckoutEnable) {
		$('#lwCryptoCheckoutContainer').addClass('lw-disabled-block-content');
	}
	$("#lwEnableCrypto").on('change', function(event) {
		isCryptoCheckoutEnable = $(this).is(":checked");
		//check is enable false then add class
		if (!isCryptoCheckoutEnable) {
			$("#lwCryptoCheckoutContainer").addClass('lw-disabled-block-content');
			//else remove class
		} else {
			$("#lwCryptoCheckoutContainer").removeClass('lw-disabled-block-content');
		}
	});

	//check Crypto test mode is true then disable Crypto live input field	
	if (isUseCryptoCheckoutTest) {
		$('#lwUpdateLiveCryptoCheckout').attr('disabled', true);
		$('#lwLiveCryptoInputField').addClass('lw-disabled-block-content');
		//check Crypto test mode is false then disable Crypto test input field	
	} else if (isUseCryptoCheckoutLive) {
		$('#lwUpdateTestCryptoCheckout').attr('disabled', true);
		$('#lwTestCryptoInputField').addClass('lw-disabled-block-content');
	}

	//check Crypto test mode is true on change 
	//then disable Crypto live input field	
	$("#lwUseCryptoCheckoutTest").on('change', function(event) {
		var isUseCryptoCheckoutTest = $(this).is(':checked');
		if (isUseCryptoCheckoutTest) {
			$('#lwUpdateLiveCryptoCheckout').attr('disabled', true);
			$('#lwUpdateTestCryptoCheckout').attr('disabled', false);
			$('#lwTestCryptoInputField').removeClass('lw-disabled-block-content');
			$('#lwLiveCryptoInputField').addClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is false on change 
	//then disable Crypto test input field	
	$("#lwUseCryptoCheckoutLive").on('change', function(event) {
		var isUseCryptoCheckoutLive = $(this).is(':checked');
		if (isUseCryptoCheckoutLive) {
			$('#lwUpdateTestCryptoCheckout').attr('disabled', true);
			$('#lwUpdateLiveCryptoCheckout').attr('disabled', false);
			$('#lwLiveCryptoInputField').removeClass('lw-disabled-block-content');
			$('#lwTestCryptoInputField').addClass('lw-disabled-block-content');
		}
	});

	/*********** Crypto Testing Keys setting start here ***********/
	var isTestCryptoKeysInstalled = "<?= $configurationData['crypto_testing_publishable_key'] ?>",
		lwTestCryptoInputField = $('#lwTestCryptoInputField'),
		lwTestCryptoCheckoutExists = $('#lwTestCryptoCheckoutExists');

	// Check if test Crypto keys are installed
	if (isTestCryptoKeysInstalled) {
		lwTestCryptoInputField.hide();
	} else {
		lwTestCryptoCheckoutExists.hide();
	}
	// Update Crypto checkout testing keys
	$('#lwUpdateTestCryptoCheckout').click(function() {
		$("#lwCryptoTestKeysExist").val(0);
		lwTestCryptoInputField.show();
		lwTestCryptoCheckoutExists.hide();
	});
	/*********** Crypto Testing Keys setting end here ***********/

	/*********** Crypto Live Keys setting start here ***********/
	var isLiveCryptoSecretKeysInstalled = "<?= $configurationData['crypto_live_publishable_key'] ?>",
		lwLiveCryptoInputField = $('#lwLiveCryptoInputField'),
		lwLiveCryptoCheckoutExists = $('#lwLiveCryptoCheckoutExists');

	// Check if test Crypto keys are installed
	if (isLiveCryptoSecretKeysInstalled) {
		lwLiveCryptoInputField.hide();
	} else {
		lwLiveCryptoCheckoutExists.hide();
	}
	// Update Crypto checkout testing keys
	$('#lwUpdateLiveCryptoCheckout').click(function() {
		$("#lwCryptoLiveKeysExist").val(0);
		lwLiveCryptoInputField.show();
		lwLiveCryptoCheckoutExists.hide();
	});
	/*********** Crypto Live Keys setting end here ***********/

	/*********** Crypto Enable / Disable Checkout end here ***********/
	

	//on payment setting success callback function
	function onPaymentGatewayFormCallback(responseData) {
		//check reaction code is 1 then reload view
		if (responseData.reaction == 1) {
			showConfirmation('Settings Updated Successfully', null, {
				buttons: [
					Noty.button('Reload', 'btn btn-secondary btn-sm', function() {
						__Utils.viewReload();
					})
				]
			});
		}
	};



	/*********** Razorpay Enable / Disable Checkout start here ***********/
	var isRazorpayCheckoutEnable = $('#lwEnableRazorpay').is(':checked'),
		isUseRazorpayCheckoutTest = $("#lwUseRazorpayCheckoutTest").is(':checked'),
		isUseRazorpayCheckoutLive = $("#lwUseRazorpayCheckoutLive").is(':checked');

	if (!isRazorpayCheckoutEnable) {
		$('#lwRazorpayCheckoutContainer').addClass('lw-disabled-block-content');
	}
	$("#lwEnableRazorpay").on('change', function(event) {
		isRazorpayCheckoutEnable = $(this).is(":checked");
		//check is enable false then add class
		if (!isRazorpayCheckoutEnable) {
			$("#lwRazorpayCheckoutContainer").addClass('lw-disabled-block-content');
			//else remove class
		} else {
			$("#lwRazorpayCheckoutContainer").removeClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is true then disable stripe live input field	
	if (isUseRazorpayCheckoutTest) {
		$('#lwUpdateLiveRazorpayCheckout').attr('disabled', true);
		$('#lwLiveRazorpayInputField').addClass('lw-disabled-block-content');
		//check razorpay test mode is false then disable razorpay test input field	
	} else if (isUseRazorpayCheckoutLive) {
		$('#lwUpdateTestRazorpayCheckout').attr('disabled', true);
		$('#lwTestRazorpayInputField').addClass('lw-disabled-block-content');
	}

	//check razorpay test mode is true on change
	//then disable razorpay live input field
	$("#lwUseRazorpayCheckoutTest").on('change', function(event) {
		var isUseRazorpayCheckoutTest = $(this).is(':checked');
		if (isUseRazorpayCheckoutTest) {
			$('#lwUpdateLiveRazorpayCheckout').attr('disabled', true);
			$('#lwUpdateTestRazorpayCheckout').attr('disabled', false);
			$('#lwTestRazorpayInputField').removeClass('lw-disabled-block-content');
			$('#lwLiveRazorpayInputField').addClass('lw-disabled-block-content');
		}
	});

	//check stripe test mode is false on change
	//then disable stripe test input field
	$("#lwUseRazorpayCheckoutLive").on('change', function(event) {
		var isUseRazorpayCheckoutLive = $(this).is(':checked');
		if (isUseRazorpayCheckoutLive) {
			$('#lwUpdateTestRazorpayCheckout').attr('disabled', true);
			$('#lwUpdateLiveRazorpayCheckout').attr('disabled', false);
			$('#lwLiveRazorpayInputField').removeClass('lw-disabled-block-content');
			$('#lwTestRazorpayInputField').addClass('lw-disabled-block-content');
		}
	});

	/*********** Razorpay Testing Keys setting start here ***********/
	var isTestRazorpayKeysInstalled = "<?= $configurationData['razorpay_testing_secret_key'] ?>",
		lwTestRazorpayInputField = $('#lwTestRazorpayInputField'),
		lwTestRazorpayCheckoutExists = $('#lwTestRazorpayCheckoutExists');

	// Check if test stripe keys are installed
	if (isTestRazorpayKeysInstalled) {
		lwTestRazorpayInputField.hide();
	} else {
		lwTestRazorpayCheckoutExists.hide();
	}
	// Update razorpay checkout testing keys
	$('#lwUpdateTestRazorpayCheckout').click(function() {
		$("#lwRazorpayTestKeysExist").val(0);
		lwTestRazorpayInputField.show();
		lwTestRazorpayCheckoutExists.hide();
	});
	/*********** Razorpay Testing Keys setting end here ***********/

	/*********** Razorpay Live Keys setting start here ***********/
	var isLiveRazorpaySecretKeysInstalled = "<?= $configurationData['razorpay_live_secret_key'] ?>",
		lwLiveRazorpayInputField = $('#lwLiveRazorpayInputField'),
		lwLiveRazorpayCheckoutExists = $('#lwLiveRazorpayCheckoutExists');

	// Check if test Stripe keys are installed
	if (isLiveRazorpaySecretKeysInstalled) {
		lwLiveRazorpayInputField.hide();
	} else {
		lwLiveRazorpayCheckoutExists.hide();
	}
	// Update Razorpay checkout testing keys
	$('#lwUpdateLiveRazorpayCheckout').click(function() {
		$("#lwRazorpayLiveKeysExist").val(0);
		lwLiveRazorpayInputField.show();
		lwLiveRazorpayCheckoutExists.hide();
	});
	/*********** Razorpay Live Keys setting end here ***********/

	/*********** Razorpay Enable / Disable Checkout end here ***********/

	//on payment setting success callback function
	function onPaymentGatewayFormCallback(responseData) {
		//check reaction code is 1 then reload view
		if (responseData.reaction == 1) {
			showConfirmation("{{ __tr('Settings Updated Successfully') }}", function() {
                __Utils.viewReload();
            }, {
                confirmButtonText: "{{ __tr('Reload Page') }}",
                type: "success"
            });
		}
	};
	//copy webhook url lwStripeWebhookUrl
	function copyToClipboardWebhookUrl() {
         /* Get the text field */
		 var copyTextElement = document.getElementById("lwStripeWebhookUrl");
        /* Select the text field */
        copyTextElement.select();
        copyTextElement.setSelectionRange(0, 99999); /* For mobile devices */
        /* Copy the text inside the text field */
        window.navigator.clipboard.writeText(copyTextElement.value);
	}
//copy webhook url lwRazorPayWebhookUrl
	function copyToClipboardRazorpayWebhookUrl() {
        /* Get the text field */
        var copyTextElement = document.getElementById("lwRazorPayWebhookUrl");
        /* Select the text field */
        copyTextElement.select();
        copyTextElement.setSelectionRange(0, 99999); /* For mobile devices */
        /* Copy the text inside the text field */
        window.navigator.clipboard.writeText(copyTextElement.value);
    }
	//copy webhook url lwCryptoWebhookUrl
	function copyToClipboardRazorpayWebhookUrl() {
        /* Get the text field */
        var copyTextElement = document.getElementById("lwCryptoWebhookUrl");
        /* Select the text field */
        copyTextElement.select();
        copyTextElement.setSelectionRange(0, 99999); /* For mobile devices */
        /* Copy the text inside the text field */
        window.navigator.clipboard.writeText(copyTextElement.value);
    }
</script>
@lwPushEnd