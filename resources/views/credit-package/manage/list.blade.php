@section('page-title', __tr("Manage Credit Packages"))
@section('head-title', __tr("Manage Credit Packages"))
@section('keywordName', strip_tags(__tr("Manage Credit Packages")))
@section('keyword', strip_tags(__tr("Manage Credit Packages")))
@section('description', strip_tags(__tr("Manage Credit Packages")))
@section('keywordDescription', strip_tags(__tr("Manage Credit Packages")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Manage Credit Packages') ?></h1>
	<a class="btn btn-primary btn-sm lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" href="<?= route('manage.credit_package.add.view') ?>" title="{{ __tr('Add New Credit Package') }}"><?= __tr('Add New Credit Package') ?></a>
</div>
<!-- Start of Page Wrapper -->
<div class="row">
	<div class="col-xl-12 mb-4">
		<div class="card mb-4">
			<div class="card-body table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th><?= __tr('Image') ?></th>
							<th><?= __tr('Title') ?></th>
                            <th><abbr title="{!! __tr("Use for Google & Apple in app purchase product id") !!}"><?= __tr('Package ID') ?></abbr></th>
							<th><?= __tr('Created On') ?></th>
							<th class="text-right"><?= __tr('Price') ?></th>
							<th class="text-right"><?= __tr('Credits') ?></th>
							<th><?= __tr('Status') ?></th>
							<th><?= __tr('Action') ?></th>
						</tr>
					</thead>
					<tbody>
						@if(!__isEmpty($creditPackageData))
						@foreach($creditPackageData as $package)
						<tr id="lw-package-row-<?= $package['_uid'] ?>">
							<td class="lw-photoswipe-gallery">
								<img src="<?= $package['packageImageUrl'] ?>" class="img-thumbnail lw-item-img-thumbnail lw-photoswipe-gallery-img" />
							</td>
							<td><?= $package['title'] ?></td>
                            <td><?= toggleProductId($package['_uid']) ?></td>
							<td><?= $package['created_at'] ?></td>
							<td class="text-right"><?= priceFormat($package['price'], true) ?></td>
							<td class="text-right"><?= $package['credits'] ?></td>
							<td><?= $package['status'] ?></td>
							<td>
								<div class="btn-group">
									<button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="fas fa-ellipsis-v"></i>
									</button>
									<div class="dropdown-menu dropdown-menu-right">
										<a class="dropdown-item lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" data-title="{{ __tr('Edit Credit Package') }}" href="<?= route('manage.credit_package.edit.view', ['packageUId' => $package['_uid']]) ?>"><i class="far fa-edit"></i> <?= __tr('Edit') ?></a>
										<a data-callback="onDelete" data-method="post" class="dropdown-item lw-ajax-link-action" href="<?= route('manage.credit_package.write.delete', ['packageUId' => $package['_uid']]) ?>"><i class="fas fa-trash-alt"></i> <?= __tr('Delete') ?></a>
									</div>
								</div>
							</td>
						</tr>
						@endforeach
						@endif
						@if(__isEmpty($creditPackageData))
						<tr>
							<td colspan="7" class="text-center">
								<?= __tr('There are no records.') ?>
							</td>
						</tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- End of Page Wrapper -->
@lwPush('appScripts')
<script>
	function onDelete(response) {
		//check reaction code is 1
		if (response.reaction == 1) {
			//apply class row fade in
			$("#lw-package-row-" + response.data.packageUId).addClass("lw-deleted-row");
		}
	}
</script>
@lwPushEnd