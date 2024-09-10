@section('page-title', __tr("Manage Pages"))
@section('head-title', __tr("Manage Pages"))
@section('keywordName', strip_tags(__tr("Manage Pages")))
@section('keyword', strip_tags(__tr("Manage Pages")))
@section('description', strip_tags(__tr("Manage Pages")))
@section('keywordDescription', strip_tags(__tr("Manage Pages")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Manage Pages') ?></h1>
	<a class="btn btn-primary btn-sm lw-ajax-link-action lw-action-with-url" href="<?= route('manage.page.add.view') ?>" title="Add New Page"><?= __tr('Add New Page') ?></a>
</div>
<!-- Start of Page Wrapper -->
<div class="row">
	<div class="col-xl-12 mb-4">
		<div class="card mb-4">
			<div class="card-body">
				<x-lw.datatable id="lwManagePagesTable" :url="route('manage.page.list')">
                    <th  data-orderable="true" name="title"><?= __tr('Title') ?></th>
                        <th data-order-by="true" data-order-type="desc" data-orderable="true" data-name="created_at"><?= __tr('Created') ?></th>
                        <th  data-name="updated_at"><?= __tr('Updated') ?></th>
                        <th data-name="status"><?= __tr('Status') ?></th>
                        <th data-template="#pagesActionColumnTemplate" name="null"><?= __tr('Action') ?></th>
                </x-lw.datatable>
			</div>
		</div>
	</div>
</div>
<!-- End of Page Wrapper -->

<!-- User Soft delete Container -->
<div id="lwPageDeleteContainer" style="display: none;">
	<h3><?= __tr('Are You Sure!') ?></h3>
	<strong><?= __tr('You want to delete this page.') ?></strong>
</div>
<!-- User Soft delete Container -->

<!-- Pages Action Column -->
<script type="text/_template" id="pagesActionColumnTemplate">
	<div class="btn-group">
		<button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="fas fa-ellipsis-v"></i>
		</button>
		<div class="dropdown-menu dropdown-menu-right">
		    <!-- Page Edit Button -->
		    <a class="dropdown-item lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Edit Page') }}" href="<%= __Utils.apiURL("<?= route('manage.page.edit.view', ['pageUId' => 'pageUId']) ?>", {'pageUId': __tData._uid}) %>"><i class="far fa-edit"></i> <?= __tr('Edit') ?></a>
		    <!-- /Page Edit Button -->

		    <!-- Preview URL -->
		    <a class="dropdown-item" target="_blank" href="<%= __tData.preview_url %>"><i class="fas fa-external-link-alt"></i> <?= __tr('Page Link') ?></a>
		    <!-- /Preview URL -->

		    <!-- Page Delete Button -->
		    <a data-callback="onSuccessAction"  data-callback-params="{{ json_encode(['datatableId' => '#lwManagePagesTable']) }}" data-method="post" class="dropdown-item lw-ajax-link-action-via-confirm" data-confirm="#lwPageDeleteContainer" href data-action="<%= __Utils.apiURL("<?= route('manage.page.write.delete', ['pageUId' => 'pageUId']) ?>", {'pageUId': __tData._uid}) %>"><i class="fas fa-trash-alt"></i> <?= __tr('Delete') ?></a>
		    <!-- /Page Delete Button -->

		</div>
	</div>
</script>
<!-- Pages Action Column -->

@lwPush('appScripts')
<script>
//   Perform actions after delete
        var onSuccessAction = function(response, params) {
		reloadDT(params.datatableId);
	}
</script>
@lwPushEnd