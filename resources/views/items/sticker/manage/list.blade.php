@section('page-title', __tr("Manage Stickers"))
@section('head-title', __tr("Manage Stickers"))
@section('keywordName', strip_tags(__tr("Manage Stickers")))
@section('keyword', strip_tags(__tr("Manage Stickers")))
@section('description', strip_tags(__tr("Manage Stickers")))
@section('keywordDescription', strip_tags(__tr("Manage Stickers")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Manage Stickers') ?></h1>
	<a class="btn btn-primary btn-sm lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" href="<?= route('manage.item.sticker.add.view') ?>" title="{{ __tr('Add New Sticker') }}">{{ __tr('Add New Sticker') }}</a>
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
							<th><?= __tr('Created On') ?></th>
							<th><?= __tr('Normal Price (In Credits)') ?></th>
							<th><?= __tr('Premium Price (In Credits)') ?></th>
							<th><?= __tr('Status') ?></th>
							<th><?= __tr('Is Premium') ?></th>
							<th><?= __tr('Action') ?></th>
						</tr>
					</thead>
					<tbody>
						@if(!__isEmpty($stickerListData))
						@foreach($stickerListData as $stickerData)
						<tr id="lw-sticker-row-<?= $stickerData['_uid'] ?>">
							<td class="lw-photoswipe-gallery">
								<img src="<?= $stickerData['stickerImageUrl'] ?>" class="img-thumbnail lw-item-img-thumbnail lw-photoswipe-gallery-img" />
							</td>
							<td><?= $stickerData['title'] ?></td>
							<td><?= $stickerData['created_at'] ?></td>
							<td><?= $stickerData['normal_price'] ?></td>
							<td><?= $stickerData['premium_price'] ?></td>
							<td><?= $stickerData['status'] ?></td>
							<td><?= $stickerData['is_premium_sticker'] ?></td>
							<td>
								<div class="btn-group">
									<button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="fas fa-ellipsis-v"></i>
									</button>
									<div class="dropdown-menu dropdown-menu-right">
										<a class="dropdown-item lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" data-title="{{ __tr('Edit Sticker') }}" href="<?= route('manage.item.sticker.edit.view', ['stickerUId' => $stickerData['_uid']]) ?>"><i class="far fa-edit"></i> <?= __tr('Edit') ?></a>
										<a data-callback="onDelete" data-method="post" class="dropdown-item lw-ajax-link-action" href="<?= route('manage.item.sticker.write.delete', ['stickerUId' => $stickerData['_uid']]) ?>"><i class="fas fa-trash-alt"></i> <?= __tr('Delete') ?></a>
									</div>
								</div>
							</td>
						</tr>
						@endforeach
						@endif
						@if(__isEmpty($stickerListData))
						<tr>
							<td colspan="8" class="text-center">
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
			$("#lw-sticker-row-" + response.data.stickerUId).addClass("lw-deleted-row");
		}
	}
</script>
@lwPushEnd