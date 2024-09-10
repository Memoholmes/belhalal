@section('page-title', __tr("Financial Transactions"))
@section('head-title', __tr("Financial Transactions"))
@section('keywordName', strip_tags(__tr("Financial Transactions")))
@section('keyword', strip_tags(__tr("Financial Transactions")))
@section('description', strip_tags(__tr("Financial Transactions")))
@section('keywordDescription', strip_tags(__tr("Financial Transactions")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Financial Transactions') ?></h1>
</div>
<!-- Start of Page Wrapper -->
<?php $transactionType = request()->transactionType; ?>
<div class="row">
	<div class="col-xl-12 mb-4">
		<div class="card mb-4">
			<div class="card-body">
				<ul class="nav nav-tabs mb-3">
					<!-- Live Transaction -->
					<li class="nav-item">
						<a class="nav-link <?= $transactionType == 'live' ? 'active' : '' ?> lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Financial Transactions: Live') }}" href="<?= route('manage.financial_transaction.read.view_list', ['transactionType' => 'live']) ?>">
							<?= __tr('Live Transaction') ?>
						</a>
					</li>
					<!-- /Live Transaction -->

					<!-- Test Transaction -->
					<li class="nav-item">
						<a class="nav-link <?= $transactionType == 'test' ? 'active' : '' ?> lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Financial Transactions: Test') }}" href="<?= route('manage.financial_transaction.read.view_list', ['transactionType' => 'test']) ?>">
							<?= __tr('Test Transaction') ?>
						</a>
					</li>
					<!-- /Test Transaction -->
				</ul>
				<!-- delete all transaction button -->
				@if($transactionType == 'test')
				<a class="btn btn-danger float-right btn-sm lw-ajax-link-action-via-confirm" data-confirm="#lwDeleteAllTestTransactions" data-method="post" data-action="<?= route('manage.financial_transaction.write.delete.all_transaction') ?>" data-callback="onSuccessAction"  data-callback-params="{{ json_encode(['datatableId' => '#lwTransactionTable']) }}" ><?= __tr('Delete All') ?></a>
				<br><br>
				@endif
				<!-- delete all transaction button -->

				<!-- transaction table -->
				<x-lw.datatable id="lwTransactionTable" :url="route('manage.financial_transaction.read.list', ['transactionType' => $transactionType])">
                    <th  data-orderable="true" data-name="userFullName"><?= __tr('User') ?></th>
                        <th  data-order-by="true" data-order-type="desc" data-orderable="true"  data-name="created_at"><?= __tr('Created On') ?></th>
                        <th  data-orderable="false" data-name="formatAmount"><?= __tr('Amount') ?></th>
						<th  data-orderable="true"  data-name="method"><?= __tr('Payment Method') ?></th>
                        <th  data-orderable="true" data-name="status"><?= __tr('Status') ?></th>
                        <th    data-name="packageName"><?= __tr('Package') ?></th>
                        <th data-template="#transactionDetailsActionColumnTemplate" name="null"><?= __tr('Action') ?></th>
                </x-lw.datatable>
				<!-- /transaction table -->
			</div>
		</div>
	</div>
</div>
<!-- End of Page Wrapper -->

<!-- User Permanent delete Container -->
<div id="lwDeleteAllTestTransactions" style="display: none;">
	<h3><?= __tr('Are You Sure!') ?></h3>
	<strong><?= __tr('You want to delete all test transactions.') ?></strong>
</div>
<!-- User Permanent delete Container -->
<!-- Transaction Details Action Column -->
<script type="text/_template" id="transactionDetailsActionColumnTemplate">
	<!-- action dropdown -->
	<% if(!_.isEmpty(__tData.__data)) { %>
	<div class="btn-group">
		<button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="fas fa-ellipsis-v"></i>
		</button>
		<div class="dropdown-menu dropdown-menu-right">
			<!-- Transaction Detail Button -->
			<a href class="dropdown-item" data-toggle="modal" data-raw-data='<%= JSON.stringify(__tData.__data) %>' data-target="#userTransactionDetailDialog" data-transaction-detail><i class="far fa-edit" id="lwTransactionDetailBtn"></i> <?= __tr('Financial Transaction') ?></a>
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
				<div class="card-body">
					<div class="overflow-auto">
						<%= __tData.rawPaymentData %>
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
			'rawPaymentData': data['rawData']['rawPaymentData']
		};
	}, function(e, myData) {});

	//delete all test transaction callback
	var onSuccessAction = function(response, params) {
		reloadDT(params.datatableId);
	}
</script>
@lwPushEnd