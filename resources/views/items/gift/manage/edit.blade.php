 <!-- Page Heading -->
 <div class="d-sm-flex align-items-center justify-content-between mb-4">
 	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Edit Gift')  ?></h1>
 	<!-- back button -->
 	<a class="btn btn-light btn-sm lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Manage Gifts') }}" href="<?= route('manage.item.gift.view') ?>">
 		<i class="fa fa-arrow-left" aria-hidden="true"></i> <?= __tr('Back to Gifts') ?>
 	</a>
 	<!-- /back button -->
 </div>
 <!-- Start of Page Wrapper -->
 <div class="row">
 	<div class="col-xl-12 mb-4">
 		<div class="card mb-4">
 			<div class="card-body">
 				<!-- page add form -->
 				<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.item.gift.write.edit', ['giftUId' => $giftEditData['_uid']]) ?>">

 					<div class="row">
 						<div class="col-lg-6">
 							<input type="file" class="lw-file-uploader" data-instant-upload="true" data-action="<?= route('media.gift.upload_temp_media') ?>" data-remove-media="true" data-callback="afterUploadedFile" data-allow-image-preview="false" data-allowed-media='<?= getMediaRestriction('gift') ?>'>
 							<input type="hidden" name="gift_image" class="lw-uploaded-file" value="">
 						</div>
 						<div class="col-lg-6" id="lwGiftImagePreview">
 							<img class="lw-gift-preview-image lw-uploaded-preview-img" src="<?= $giftEditData['gift_image_url'] ?>">
 						</div>
 					</div>

 					<!-- title input field -->
 					<div class="form-group">
 						<label for="lwTitle"><?= __tr('Title') ?></label>
 						<input type="text" value="<?= $giftEditData['title'] ?>" id="lwTitle" class="form-control" name="title" placeholder="<?= __tr('Title')  ?>" required minlength="3">
 					</div>
 					<!-- / title input field -->
 					<div class="form-group row">
 						<!-- normal price field -->
 						<div class="col-sm-6 mb-3 mb-sm-0">
 							<label for="lwNormalPrice"><?= __tr('Normal Price') ?></label>
 							<div class="input-group">
 								<input type="number" value="<?= $giftEditData['normal_price'] ?>" id="lwNormalPrice" class="form-control" name="normal_price" required digits="true">
 								<div class="input-group-append">
 									<span class="input-group-text"><?= __tr('Credits') ?></span>
 								</div>
 							</div>
 						</div>
 						<!-- / normal price field -->

 						<!-- premium price field -->
 						<div class="col-sm-6 mb-3 mb-sm-0">
 							<label for="lwPremiumPrice"><?= __tr('Premium Price') ?></label>
 							<div class="input-group">
 								<input type="number" value="<?= $giftEditData['premium_price'] ?>" id="lwPremiumPrice" class="form-control" name="premium_price" required digits="true">
 								<div class="input-group-append">
 									<span class="input-group-text"><?= __tr('Credits') ?></span>
 								</div>
 							</div>
 						</div>
 						<!-- / premium price field -->
 					</div>

 					<!-- status field -->
 					<div class="custom-control custom-checkbox custom-control-inline">
 						<input type="checkbox" class="custom-control-input" id="statusCheck" name="status" <?= $giftEditData['status'] == 1 ? 'checked' : '' ?>>
 						<label class="custom-control-label" for="statusCheck"><?= __tr('Active')  ?></label>
 					</div>
 					<!-- / status field -->
 					<br><br>
 					<!-- Update button -->
 					<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile"><?= __tr('Update')  ?></button>
 					<!-- / Update button -->
 				</form>
 				<!-- / page add form -->
 			</div>
 		</div>
 	</div>
 </div>
 <!-- End of Page Wrapper -->
 @lwPush('appScripts')
 <script>
 	function afterUploadedFile(responseData) {
 		if (responseData.reaction == 1) {
 			$('.lw-gift-preview-image').attr('src', responseData.data.path);
 		}
 	}
 </script>
 @lwPushEnd