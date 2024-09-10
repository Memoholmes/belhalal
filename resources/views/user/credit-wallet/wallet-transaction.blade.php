@section('page-title', __tr('Wallet Transactions'))
@section('head-title', __tr('Wallet Transactions'))
@section('keywordName', __tr('Wallet Transactions'))
@section('keyword', __tr('Wallet Transactions'))
@section('description', __tr('Wallet Transactions'))
@section('keywordDescription', __tr('Wallet Transactions'))
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
		<i class="fas fa-coins fa-fw mr-2"></i> <?= __trn('__creditBalance__ Credit', '__creditBalance__ Credits', $totalUserCreditsAvailable, [
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
		<li class="nav-item" role="presentation">
			<a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.credit_wallet.read.view') ?>">
				<?= __tr('Buy Credits') ?>
			</a>
		</li>
		<li class="nav-item disabled" role="presentation">
			<a class="nav-link active disabled" href="<?= route('user.wallet.transactions.read.view') ?>">
				<?= __tr('Wallet Transactions') ?>
			</a>
		</li>
	</ul>
	 <!-- transaction list card -->
     <div class="card mb-4 mt-4">
        <!-- card body -->
        <div class="card-body">
            <!-- financial transaction list -->
            <h4 class="mt-3"><?= __tr('Wallet Transactions') ?></h4>
            <hr>
            <!-- / financial transaction list -->

            <!-- financial transaction table -->
			<x-lw.datatable id="lwUserTransactionTable" :url="route('user.credit_wallet.read.wallet_transaction_list')">
				<th data-order-by="true" data-order-type="desc" data-orderable="true" data-name="created_at"><?= __tr('Transaction On') ?></th>
					<th  data-orderable="false"  data-name="formattedTransactionType"><?= __tr('Transaction For') ?></th>
					<th  data-orderable="true" data-name="credits"><?= __tr('Credits (credited/debited)') ?></th>
					<th data-template="#transactionDetailsActionColumnTemplate" name="null"><?= __tr('Action') ?></th>
			</x-lw.datatable>

            <!-- financial transaction table -->
        </div>
        <!-- / card body -->
    </div>
    <!-- / transaction list card -->


</div>
<!-- /buy credits card -->

<!-- Transaction Details Action Column -->
<script type="text/_template" id="transactionDetailsActionColumnTemplate">
	<!-- action dropdown -->
	<% if(__tData.transactionType == 1 && !_.isEmpty(__tData.financialTransactionDetail)) { %>
	<div class="btn-group">
		<button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="fas fa-ellipsis-v"></i>
		</button>
		<div class="dropdown-menu dropdown-menu-right">
			<!-- Transaction Detail Button -->
			<a href class="dropdown-item" data-toggle="modal" data-financial-transaction='<%= JSON.stringify(__tData.financialTransactionDetail) %>' data-target="#userTransactionDetailDialog" data-transaction-detail><i class="far fa-edit" id="lwTransactionDetailBtn"></i> <?= __tr('Financial Transaction') ?></a>
			<!-- /Transaction Detail Button -->
		</div>
	</div>
	<% } else { %>
	-
	<% } %>
	<!-- /action dropdown -->
</script>
<!-- Transaction Details Action Column -->

<!-- user transaction Modal-->
<div class="modal fade" id="userTransactionDetailDialog" tabindex="-1" role="dialog" aria-labelledby="userTransactionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="userTransactionModalLabel"><?= __tr('Financial Transaction') ?></h5>
				<button class="close" type="button" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body" id="lwUserTransactionContent"></div>
			<script type="text/_template" id="lwTransactionDetailTemplate" data-replace-target="#lwUserTransactionContent" data-modal-id="#userTransactionDetailDialog">
				<div>
						<div class="card-body">
							<ul class="list-group list-group-flush">
								<li class="list-group-item">
									<?= __tr('Created On') ?>
									<span class="float-right"><%- __tData.financialTransactionData.created_at %></span>
								</li>
								<li class="list-group-item">
									<?= __tr('Amount') ?>
									<span class="float-right"><%= __tData.financialTransactionData.amount %></span>
								</li>
								<li class="list-group-item">
									<?= __tr('Currency') ?>
									<span class="float-right"><%= __tData.financialTransactionData.currency_code %></span>
								</li>
								<li class="list-group-item">
									<?= __tr('Status') ?>
									<span class="float-right"><%= __tData.financialTransactionData.status %></span>
								</li>
								<li class="list-group-item">
									<?= __tr('Method') ?>
									<span class="float-right"><%= __tData.financialTransactionData.method %></span>
								</li>
								<li class="list-group-item">
									<?= __tr('Mode') ?>
									<span class="float-right"><%= __tData.financialTransactionData.payment_mode %></span>
								</li>
							</ul>
						</div>
					</div>
			</script>
			<!-- modal footer -->
			<div class="modal-footer mt-3">
				<button class="btn btn-light btn-sm" class="close" type="button" data-dismiss="modal"><?= __tr('Close') ?></button>
			</div>
			<!-- modal footer -->
		</div>
	</div>
</div>
<!-- / user transaction Modal-->
@lwPush('appScripts')
<script>
	//user transaction dialog details
	__Utils.modalTemplatize('#lwTransactionDetailTemplate', function(e, data) {
		return {
			'financialTransactionData': data['financialTransaction']
		};
	}, function(e, myData) {});

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

</script>
@lwPushEnd