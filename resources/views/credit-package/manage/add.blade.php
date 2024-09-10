 <!-- Page Heading -->
 <div class="d-sm-flex align-items-center justify-content-between mb-4">
 	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Add Credit Package')  ?></h1>
 	<!-- back button -->
 	<a class="btn btn-light btn-sm lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Manage Credit Packages') }}" href="<?= route('manage.credit_package.read.list') ?>">
 		<i class="fa fa-arrow-left" aria-hidden="true"></i> <?= __tr('Back to Credit Packages') ?>
 	</a>
 	<!-- /back button -->
 </div>
 <!-- Start of Package Wrapper -->
 <div class="row">
 	<div class="col-xl-12 mb-4">
 		<div class="card mb-4">
 			<div class="card-body">
 				<!-- package add form -->
 				<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.credit_package.write.add') ?>">
 					<div class="row">
 						<div class="col-lg-6">
 							<input type="file" class="lw-file-uploader" data-instant-upload="true" data-action="<?= route('media.package.upload_temp_media') ?>" data-remove-media="true" data-callback="afterUploadedFile" data-allow-image-preview="false" data-allowed-media='<?= getMediaRestriction('package') ?>'>
 							<input type="hidden" name="package_image" class="lw-uploaded-file" value="" required>
 						</div>
 						<div class="col-lg-6" style="display: none" id="lwPackageImagePreview">
 							<img class="lw-package-preview-image lw-uploaded-preview-img" src="">
 						</div>
 					</div>

 					<!-- title input field -->
 					<div class="form-group">
 						<label for="lwTitle"><?= __tr('Title') ?></label>
 						<input type="text" class="form-control d-block" name="title" id="lwTitle" required minlength="3">
 					</div>
 					<!-- / title input field -->
 					<div class="form-group row">
 						<!-- price field -->
 						<div class="col-sm-6 mb-3 mb-sm-0">
 							<label for="lwPrice"><?= __tr('Price') ?></label>
                             <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="lwPriceAddon">{{  getCurrencySymbol() }}</span>
                                </div>
                                <input type="number" class="form-control d-block" name="price" id="lwPrice" required digits="true">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="lwPriceAddon">{{  getCurrency() }}</span>
                                </div>
                            </div>
 						</div>
 						<!-- / price field -->

 						<!-- Credits field -->
 						<div class="col-sm-6 mb-3 mb-sm-0">
 							<label for="lwCredit"><?= __tr('Credits') ?></label>
 							<input type="number" class="form-control d-block" name="credits" id="lwCredit" required digits="true">
 						</div>
 						<!-- / Credits field -->
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
 				<!-- / package add form -->
 			</div>
 		</div>
 	</div>
 </div>
 <!-- End of Page Wrapper -->
 @lwPush('appScripts')
 <script>
 	function afterUploadedFile(responseData) {
 		if (responseData.reaction == 1) {
 			$("#lwPackageImagePreview").show();
 			$('.lw-package-preview-image').attr('src', responseData.data.path);
 		}
 	}
 </script>
 @lwPushEnd