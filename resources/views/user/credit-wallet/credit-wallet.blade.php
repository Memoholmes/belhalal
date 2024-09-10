@section('page-title', __tr('Credit Wallet'))
@section('head-title', __tr('Credit Wallet'))
@section('keywordName', __tr('Credit Wallet'))
@section('keyword', __tr('Credit Wallet'))
@section('description', __tr('Credit Wallet'))
@section('keywordDescription', __tr('Credit Wallet'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<style>
	.lw-group-radio-option-img.active::after {
		content: "<?= __tr('Selected') ?>";
	}
	
</style>

<!-- Show loader when process payment request -->
<div class="d-flex justify-content-center">
	<div class="lw-page-loader lw-show-till-loading">
		<div class="spinner-border text-primary" role="status"></div>
	</div>
</div>
<!-- Show loader when process payment request -->

<div class="d-block text-center lw-credit-balance">
	<h2 class="text-gray-200">
		<?= __tr('Your Wallet Balance') ?>
	</h2>
	<h1 class="text-primary">
		<?php $totalUserCreditsAvailable = totalUserCredits(); ?>
		<i class="fas fa-coins fa-fw mr-2"></i>
		<?= __trn('__creditBalance__ Credit', '__creditBalance__ Credits', $totalUserCreditsAvailable, [
													'__creditBalance__' => $totalUserCreditsAvailable
												]) ?>
	</h1>
	<hr>
	<p class="text-muted ">
		<?= __tr("You can use these credits on this website for the various purchases like to buy Premium Membership, Profile Booster, Gift & Sticker purchases etc") ?>
	</p>
</div>

<!-- buy credits card -->
<div>
	<!-- payment successfully message -->
	@if(session('success'))
	<!--  success message when email sent  -->
	<div class="alert alert-success alert-dismissible">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<?= session('message') ?>
	</div>
	<!--  /success message when email sent  -->
	@endif
	<!-- / payment successfully message -->

	<!-- payment failed message -->
	@if(session('error'))
	<!--  danger message when email sent  -->
	<div class="alert alert-danger alert-dismissible">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<?= session('message') ?>
	</div>
	<!--  /danger message when email sent  -->
	@endif
	<!-- / payment failed message -->

	<!--  success messages  -->
	<div class="alert alert-success alert-dismissible fade show" id="lwSuccessMessage" style="display:none;"></div>
	<!--  /success messages  -->

	<!--  error messages  -->
	<div class="alert alert-danger alert-dismissible fade show" id="lwErrorMessage" style="display:none;"></div>
	<!--  /error messages  -->
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item disabled" role="presentation">
			<a class="nav-link active disabled" href="<?= route('user.credit_wallet.read.view') ?>">
				<?= __tr('Buy Credits') ?>
			</a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link lw-ajax-link-action lw-action-with-url"
				href="<?= route('user.wallet.transactions.read.view') ?>">
				<?= __tr('Wallet Transactions') ?>
			</a>
		</li>
	</ul>
	<h4 class="mt-4">
		<?= __tr('Buy More Credits') ?>
	</h4>
	<small class="text-muted">{{ __tr('Please select package to purchase credits.') }}</small>
	<hr>
	<!-- select package form -->
	<form class="lw-ajax-form lw-form text-center" name="credit_wallet_form" method="post"
		action="<?= route('user.credit_wallet.write.payment_process') ?>" data-callback="onSuccessCallback">
		<!-- show credit packages radio options -->
		<div class="btn-group-toggle lw-img-credits-radio-btns-container" data-toggle="buttons">
			@if(isset($creditWalletData) and !__isEmpty($creditWalletData['creditPackages']))
			@foreach($creditWalletData['creditPackages'] as $key => $package)
			<span class="btn lw-group-radio-option-img">
				<span class="lw-credit-package-name">
					<?= $package['package_name'] ?>
				</span>
				<input type="radio" value="<?= $package['_uid'] ?>" data-package-price="<?= $package['price'] ?>"
					data-package-name="<?= $package['package_name'] ?>" name="select_package" />
				<div>
					<img src="<?= $package['packageImageUrl'] ?>" />
					<h3 class="text-success">
						<?= __trn('__credits__ Credit', '__credits__ Credits', $package['credit'], [
							'__credits__' => $package['credit']
						]) ?>
					</h3>
					<span>
						<?= __tr('for __currencyCode__ __price__ only', [
							'__currencyCode__' => getStoreSettings('currency_symbol'),
							'__price__' => $package['price']
						]) ?>
					</span>
				</div>
			</span>
			@endforeach
			@else
			<!-- info message -->
			<div class="alert alert-info">
				<?= __tr('There are no packages') ?>
			</div>
			<!-- / info message -->
			@endif
		</div>
		<!-- / show credit packages radio options -->

		<!-- hidden select payment option input field -->
		<input type="hidden" name="select_payment_method" id="lwSelectPaymentMethod" />
		<!-- / hidden select payment option input field -->

		<!-- payment buttons -->
		<div id="lwPaymentOption" style="display:none">
			@if(getStoreSettings('enable_paypal'))
			<div id="paypal-button-container"></div>
			@endif

			@if(getStoreSettings('enable_stripe'))
			<button
				class="lw-ajax-form-submit-action btn lw-btn-block-mobile lw-stripe-checkout-btn lw-stripe-payment-btn lw-payment-checkout-btn"
				title="<?= __tr('Stripe Payment') ?>">
				<img class="lw-payment-img" src="<?= asset('imgs/payment-images/stripe-payment.svg') ?>"
					alt="<?= __tr('Stripe') ?>">
			</button>
			@endif

			@if(getStoreSettings('enable_razorpay'))
			<button class="btn lw-payment-checkout-btn" id="lwRazorPayBtn" title="<?= __tr('Razorpay Payment') ?>"><img
					class="lw-payment-img" src="<?= asset('imgs/payment-images/razorpay-payment.svg') ?>"
					alt="<?= __tr('Razorpay') ?>"></button>
			@endif

			@if(getStoreSettings('enable_coingate'))
			<button class="btn lw-payment-checkout-btn" id="lwCoingateBtn" type="button"
				title="<?= __tr('Coingate Payment') ?>"><img class="lw-payment-img"
					src="<?= asset('imgs/payment-images/coingate-payment.svg') ?>" alt="<?= __tr('Coingate') ?>">
			</button>
			@endif

			{{-- crypto btn start --}}
			@if(getStoreSettings('enable_crypto'))
			<div class="d-flex justify-content-center">
				<div id="crypto-pay-button" class="bg-white p-4 rounded lw-crypto-pay-button"></div>
			</div>
			@endif
			{{-- crypto btn end --}}

		</div>
		<!-- / payment buttons -->


	</form>


	<!-- /select package form -->
</div>
<!-- /buy credits card -->
{{-- paypal keys--}}
@if(getStoreSettings('enable_paypal'))
@if(getStoreSettings('use_test_paypal_checkout'))
<script
	src="https://www.paypal.com/sdk/js?client-id=<?= getStoreSettings('paypal_checkout_testing_client_id') ?>&currency=<?= getStoreSettings('currency') ?>">
</script>
@else
<script
	src="https://www.paypal.com/sdk/js?client-id=<?= getStoreSettings('paypal_checkout_live_client_id') ?>&currency=<?= getStoreSettings('currency') ?>">
</script>
@endif
@endif
{{-- stripe keys--}}
@if(getStoreSettings('enable_stripe'))
<script src="https://js.stripe.com/v3/"></script>
@endif
{{-- razorpay keys--}}
@if(getStoreSettings('enable_razorpay'))
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
{{-- crypto start--}}
@if(getStoreSettings('enable_crypto'))
@if(getStoreSettings('crypto_testing_publishable_key'))
<script src="https://js.crypto.com/sdk?publishable-key=<?= getStoreSettings('crypto_testing_publishable_key') ?>">
</script>
@else
<script src="https://js.crypto.com/sdk?publishable-key=<?= getStoreSettings('crypto_live_publishable_key') ?>"></script>
@endif
@endif
{{-- crypto end --}}


@lwPush('appScripts')
<script>
	$(document).ready(function() {
		var enablePaypalCheckout = '<?= getStoreSettings('enable_paypal') ?>',
			enableRazorpayCheckout = '<?= getStoreSettings('enable_razorpay') ?>',
			useTestRazorpayCheckout = '<?= getStoreSettings('use_test_razorpay') ?>',
			enableCoingate = '<?= getStoreSettings('enable_coingate') ?>',
			useTestCoingate = '<?= getStoreSettings('use_test_coingate') ?>',
			enableCrypto = '<?= getStoreSettings('enable_crypto') ?>';

		//set on click select payment option
		$(".lw-stripe-checkout-btn").on('click', function() {
			$("#lwSelectPaymentMethod").val('stripe');
		});

		//by default hide payment options
		$('input[type=radio][name=select_package]').on('change', function(event) {
			var $this = $(this),
				packageUid = event.target.value,
				packageName = $this.attr('data-package-name'),
				packagePrice = $this.attr('data-package-price');
			//on change show payment button options
			$("#lwPaymentOption").show();
            $('#lwErrorMessage, #lwSuccessMessage').hide();

			/*************************************************************************************************************
			 RazorPay Payment on Click
			**************************************************************************************************************/
			if (enableRazorpayCheckout) {
				var razorpayKey = null;
				if (useTestRazorpayCheckout) {
					razorpayKey = '<?= getStoreSettings('razorpay_testing_key') ?>';
				} else {
					razorpayKey = '<?= getStoreSettings('razorpay_live_key') ?>';
				}

				$("#lwRazorPayBtn").on('click', function() {
					try {
						var options = {
							"key": razorpayKey,
							"amount": getRazorPayAmount(packagePrice).toFixed(2), // 2000 paisa = INR 20
							"currency": "<?= getStoreSettings('currency'); ?>",
							"name": packageName,
							handler: function(response) {
								if (!_.isEmpty(response.razorpay_payment_id)) {
									//before process on server disabled payment button block
									$("#lwPaymentOption").addClass('lw-disabled-block-content');
									//show loader before ajax request
									$(".lw-show-till-loading").show();
									var razorPayRequestUrl = __Utils.apiURL("<?= route('user.credit_wallet.write.razorpay.checkout') ?>");
									//post ajax request
									__DataRequest.post(razorPayRequestUrl, {
										'packageUid': packageUid,
										'razorpayPaymentId': response.razorpay_payment_id
									}, function(response) {
										//handle callback event data
										handlePaymentCallbackEvent(response);
									});
								} else {
									// Show a cancel page, or return to cart
									//bind error message on div
									$("#lwErrorMessage").text('<?= __tr("Payment Failed") ?>');
									//show hide div
									$("#lwErrorMessage").toggle();
									_.delay(function() {
										//hide div
										$("#lwErrorMessage").toggle();
									}, 10000);
								}
							},
							"prefill": {
								"name": '<?= getUserAuthInfo('profile.full_name') ?>',
								"email": '<?= getUserAuthInfo('profile.email') ?>'
							},
							"notes": {
								"packageUid": packageUid,
								"userId": '<?=getUserID()?>',
							},
							"theme": {
								"color": "#050505"
							},
							"modal": {
								ondismiss: function(e) {}
							}
						};
						var rzp1 = new Razorpay(options); // will inherit key and image from above.
						rzp1.open();
					} catch (error) {
						//bind error message on div
						alert(error.message);
					}
				});
			}

			//if paypal button instance available then remove from dom else create instance
			if (!_.isEmpty($("#paypal-button-container").html())) {
				$("#paypal-button-container").empty();
			}
			if (!_.isEmpty($("#crypto-pay-button").html())) {
				$("#crypto-pay-button").empty();
			}
			

			//paypal payment button script js
			/*************************************************************************************************************
			 Paypal Payment on Click
			**************************************************************************************************************/
			if (enablePaypalCheckout) {
				try {
					var createOrderUrl = __Utils.apiURL("<?= route('paypal.order.process')?>");
					var orderURL = __Utils.apiURL("<?= route('capture.paypal.checkout')?>");
					paypal.Buttons({
						// Order is created on the server and the order id is returned
						createOrder() {
							// console.warn(scope);
							return fetch(createOrderUrl, {
								method: "post",
								headers: {
									'content-type': 'application/json',
									'X-CSRF-TOKEN': "{{ csrf_token() }}"
								},
								body:JSON.stringify({
									'packagePrice': packagePrice,
									'packageUid' : packageUid,
									'packageName':packageName,
									'select_payment_method' : 'paypal-checkout'
								}),
							})
							.then((response) => {
								return response.json();
							})
							.then((order) => {
							
								return order.data.createPaypalOrder.id;
							});
						},
						// Finalize the transaction on the server after payer approval
						onApprove(responseData) {
							//before process on server disabled payment button block
							$("#lwPaymentOption").addClass('lw-disabled-block-content');
							//show loader before ajax request
							$(".lw-show-till-loading").show();
							// This function captures the funds from the transaction.
							return fetch(orderURL, {
								method: "post",
								headers: {
									'content-type': 'application/json',
									'X-CSRF-TOKEN': "{{ csrf_token() }}"
								},
								body: JSON.stringify({
									"orderUID": responseData.orderID
								})
							})
							.then((response) => {
								return response.clone().json();
							})
							.then((orderData) => {
								// Successful capture! For dev/demo purposes:
								handlePaymentCallbackEvent(orderData);
							});
						},
						onError: function (err) {
							// Show an error page here, when an error occurs
							alert(err.message);
						},
						onCancel: function (oncancel) {
							$("#lwErrorMessage").text('<?= __tr("Payment Canceled by User") ?>');
							//show hide div
							$("#lwErrorMessage").toggle();
							_.delay(function() {
								//hide div
								$("#lwErrorMessage").hide();
							}, 10000);
						}
					}).render('#paypal-button-container');

				} catch (error) {
					/****Add Stuff error.message ****/
					if ('<?= getStoreSettings('enable_paypal') ?>') {
						__Utils.error('<?= __tr('Something went wrong with paypal checkout, please contact to administrator.') ?>');
					}
				}
			}

			if (enableCoingate) {
				$("#lwCoingateBtn").on('click', function() {
					// console.log(packageUid, packageName, packagePrice);
					$("#lwPaymentOption").addClass('lw-disabled-block-content');
						//show loader before ajax request
						$(".lw-show-till-loading").show();
						var coinGateRequestUrl = __Utils.apiURL("<?= route('user.credit_wallet.write.coingate.checkout') ?>");
						//post ajax request
						__DataRequest.post(coinGateRequestUrl, {
							'packageUid': packageUid,
							'packageName': packageName,
							'amount': packagePrice
						}, function(response) {
							if (response.reaction == 1) {
								window.location.href = response.data.data.paymentUrl;
							}else{
                                $("#lwErrorMessage").text('<?= __tr('Something went wrong with Coingate, please contact to administrator.') ?>');
                                $("#lwPaymentOption").removeClass('lw-disabled-block-content');
                                //show loader before ajax request
                                $(".lw-show-till-loading").hide();
                                    //show hide div
                                    // $("#lwErrorMessage").show();
                                    _.delay(function() {
                                        //hide div
                                        $("#lwErrorMessage").hide();
                                    }, 10000);
								__Utils.error('<?= __tr('Something went wrong with Coingate, please contact to administrator.') ?>');
							}
						});
				});
			}

			///crypto payment button
			//crypto payment creation

			if (enableCrypto) {
				var userCurrency="<?= getStoreSettings('currency'); ?>";
				var packageUid = event.target.value;
				var userId="<?= getUserID() ?>";
				var paymentUrl="https:js.crypto.com/sdk/payments/checkout/set_wallet?publishableKey=<?= getStoreSettings('crypto_testing_publishable_key') ?>";
				var cryptoUrl=__Utils.apiURL("<?= route('crypto.payment_process')?>");
				var cryptoCallbackUrl=__Utils.apiURL("<?= route('crypto-webhook')?>");
				//crypto start
				cryptopay.Button({
                createPayment: function(actions) {

                return actions.payment.create({

                 currency: userCurrency,
                 amount: packagePrice*100,
				 payment_url: paymentUrl,
                    metadata: {
                    size: 'XL',
                       color: 'black',
					package_Id:packageUid,
				    package_name:packageName,
					customer_id: userId,

                    }
                  });

                   },

                 onApprove: function (data,actions) {

					fetch(cryptoUrl,{
								method: "post",
								headers: {
									'content-type': 'application/json',
									'X-CSRF-TOKEN': "{{ csrf_token() }}"
								},
								body: JSON.stringify({
                                    metadata:data.metadata,
									amount:data.amount,
									status:data.status,
									cryptoPymentId:data.id,
									payment_url:data.payment_url,
									data:data,

								})

							})

							.then((response) => {
								
							//check response code
            if (response.ok) {
                return response.clone().json();
            } else {
                throw new Error('Network response was not ok');
            }
        })
        .then((responseData) => {
			handlePaymentCallbackEvent(responseData);
            // Handle the responseData as needed
			//reload page alert
            
        })

       },

                defaultLang: 'en-US' // Optional: default language for payment page
               }).render("#crypto-pay-button")

			}
			//crypto end////

		});

	});

	//on success callback
	function onSuccessCallback(responseData) {
		var reactionCode = responseData.reaction,
			selectPaymentMethod = $("#lwSelectPaymentMethod").val(),
			enableStripe = "<?= getStoreSettings('enable_stripe'); ?>";
		//check reaction code
		if (reactionCode == 1 && enableStripe && selectPaymentMethod == 'stripe') {
			var requestData = responseData.data.stripeSessionData,
				useTestStripe = "<?= getStoreSettings('use_test_stripe'); ?>",
				stripePublishKey = '';

			//check is testing or live
			if (useTestStripe) {
				stripePublishKey = "<?= getStoreSettings('stripe_testing_publishable_key'); ?>";
			} else {
				stripePublishKey = "<?= getStoreSettings('stripe_live_publishable_key'); ?>";
			}

			//create stripe instance
			var stripe = Stripe(stripePublishKey);

			//check request id is not undefined
			if (typeof requestData.id !== "undefined") {
				stripe.redirectToCheckout({
					// Make the id field from the Checkout Session creation API response
					// available to this file, so you can provide it as parameter here
					sessionId: requestData.id
				}).then(function(result) {
					// If `redirectToCheckout` fails due to a browser or network
					// error, display the localized error message to your customer
					// using `result.error.message`.
					//bind error message on div
					$("#lwErrorMessage").text(result);
					//show hide div
					$("#lwErrorMessage").toggle();
					_.delay(function() {
						//hide div
						$("#lwErrorMessage").toggle();
					}, 10000);
				});
			}
		} else {
			//bind error message on div
			$("#lwErrorMessage").text(responseData.data.errorMessage);
			//show hide div
			$("#lwErrorMessage").toggle();
			_.delay(function() {
				//hide div
				$("#lwErrorMessage").toggle();
			}, 10000);
		}
	}


	/**
	 * get razor pay amount
	 *
	 *-------------------------------------------------------- */
	function getRazorPayAmount(amount) {
		return amount * 100;
	}

	/**
	 * handle callback event data hide/show data
	 *
	 *-------------------------------------------------------- */
	function handlePaymentCallbackEvent(response) {
		//hide payment options
		$("#lwPaymentOption").hide();
		//hide loader after ajax request complete
		$(".lw-show-till-loading").hide();
		//after process on server enable payment button block
		$("#lwPaymentOption").removeClass('lw-disabled-block-content');
		//check reaction code is 1
		if (response.reaction == 1) {
			//show confirmation
			showConfirmation("<?= __tr('Payment Successful, Credits has been added successfully to your wallet') ?>", function() {
                    __Utils.viewReload();
                    return;
                }, {
                showCancelBtn : false,
                type : 'success',
                confirmButtonText: "<?= __tr('Reload to Update') ?>"
			});
			//load transaction list data function
			// _.defer(function() {
			// 	reloadTransactionTable();
			// });
			//bind error message on div
			$("#lwSuccessMessage").text(response.data.message);
			//show div
			$("#lwSuccessMessage").toggle();
			_.delay(function() {
				//hide div
				$("#lwSuccessMessage").toggle();
			}, 10000);
		} else {
			//bind error message on div
			$("#lwErrorMessage").text(response.data.errorMessage);
			//show hide div
			$("#lwErrorMessage").toggle();
			_.delay(function() {
				//hide div
				$("#lwErrorMessage").toggle();
			}, 10000);
		}
	}

</script>
@lwPushEnd