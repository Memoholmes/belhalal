
 <!-- Page Heading -->
 <div class="d-sm-flex align-items-center justify-content-between mb-4">
 	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Add Gift')  ?></h1>
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
 				<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.item.gift.write.add') ?>">
 					<div class="row">
 						<div class="col-lg-6">
 							<input type="file" class="lw-file-uploader" data-instant-upload="true" data-action="<?= route('media.gift.upload_temp_media') ?>" data-remove-media="true" data-callback="afterUploadedFile" data-allow-image-preview="false" data-allowed-media='<?= getMediaRestriction('gift') ?>'>
 							<input type="hidden" name="gift_image" class="lw-uploaded-file" value="" required>
 						</div>
 						<div class="col-lg-6" style="display: none" id="lwGiftImagePreview">
 							<img class="lw-gift-preview-image lw-uploaded-preview-img" src="">
 						</div>
 					</div>
 					<!-- title input field -->
 					<div class="form-group">
 						<label for="lwTitle"><?= __tr('Title') ?></label>
 						<input type="text" class="form-control d-block" name="title" id="lwTitle" required minlength="3">
 					</div>
 					<!-- / title input field -->

 					<div class="form-group row">
 						<!-- normal price field -->
 						<div class="col-sm-6 mb-3 mb-sm-0">
 							<label for="lwNormalPrice"><?= __tr('Normal Price') ?></label>
 							<div class="input-group">
 								<input type="number" class="form-control d-block" name="normal_price" id="lwNormalPrice" required digits="true">
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
 								<input type="number" class="form-control d-block" name="premium_price" id="lwPremiumPrice" required digits="true">
 								<div class="input-group-append">
 									<span class="input-group-text"><?= __tr('Credits') ?></span>
 								</div>
 							</div>
 						</div>
 						<!-- / premium price field -->
 					</div>

 					<!-- status field -->
 					<div class="custom-control custom-checkbox custom-control-inline">
 						<input type="checkbox" class="custom-control-input" id="statusCheck" name="status">
 						<label class="custom-control-label" for="statusCheck"><?= __tr('Active')  ?></label>
 					</div>
 					<!-- / status field -->
 					<br><br>
 					<!-- add button -->
 					<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile"><?= __tr('Add')  ?></button>
 					<!-- / add button -->
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
 			$("#lwGiftImagePreview").show();
 			$('.lw-gift-preview-image').attr('src', responseData.data.path);
 		}
 	}
 </script>
 @lwPushEnd