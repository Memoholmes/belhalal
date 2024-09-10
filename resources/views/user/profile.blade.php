@section('page-title', strip_tags($userData['userName']))
@section('head-title', strip_tags($userData['userName']))
@section('page-url', url()->current())

@if(isset($userData['aboutMe']))
@section('keywordName', strip_tags($userProfileData['aboutMe']))
@section('keyword', strip_tags($userProfileData['aboutMe']))
@section('description', strip_tags($userProfileData['aboutMe']))
@section('keywordDescription', strip_tags($userProfileData['aboutMe']))
@endif

@if(isset($userData['profilePicture']))
@section('page-image', $userData['profilePicture'])
@endif
@if(isset($userData['coverPicture']))
@section('twitter-card-image', $userData['coverPicture'])
@endif

@lwPush('header')
<link rel="stylesheet" href="<?= __yesset('dist/css/vendorlibs-leaflet.css') ?>" />
<style>
	#staticMapId {
		height: 300px;
	}
</style>
@lwPushEnd
@lwPush('footer')
<script src="<?= __yesset('dist/js/vendorlibs-leaflet.js') ?>"></script>
@lwPushEnd

<?php $latitude = (__ifIsset($userProfileData['latitude'], $userProfileData['latitude'], '21.120779'));
	$longitude = (__ifIsset($userProfileData['longitude'], $userProfileData['longitude'], '79.0544606'));
?>

<!-- if user block then don't show profile page content -->
@if($isBlockUser)
<!-- info message -->
<div class="alert alert-info">
	<?= __tr('This user is unavailable.') ?>
</div>
<!-- / info message -->
@elseif($blockByMeUser)
<!-- info message -->
<div class="alert alert-info">
	<?= __tr('You have blocked this user.') ?>
</div>
<!-- / info message -->
@else
<div class="lw-contains-profile-fields">
	<div class="card mb-3">
		<div class="card-header">
			@if(!$isOwnProfile)
			<span class="float-right">
				<!-- report button -->
				<a class="text-primary btn-link btn" title="<?= __tr('Report') ?>" data-toggle="modal" data-target="#lwReportUserDialog"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
				<!-- /report button -->

				<!-- Block User button -->
				<a class="text-primary btn-link btn" title="<?= __tr('Block User') ?>" id="lwBlockUserBtn"><i class="fas fa-ban"></i></a>
				<!-- /Block User button -->
			</span>
			@endif
			<h4>
				<?= $userData['fullName'] ?>
				@if(!__isEmpty($userData['userAge'])) (<span data-model="userData.userAge"><?= __tr($userData['userAge']) ?></span>) @endif
				<!-- show user online, idle or offline status -->
				@if(!$isOwnProfile)
				@if($userOnlineStatus == 1)
				<span class="lw-dot lw-dot-success float-none" title="<?= __tr("Online") ?>"></span>
				@elseif($userOnlineStatus == 2)
				<span class="lw-dot lw-dot-warning float-none" title="<?= __tr("Idle") ?>"></span>
				@elseif($userOnlineStatus == 3)
				<span class="lw-dot lw-dot-danger float-none" title="<?= __tr("Offline") ?>"></span>
				@endif
				@endif
				<!-- /show user online, idle or offline status -->

				<!-- if user is premium then show badge -->
				@if(getFeatureSettings('premium_badge'))
				<i class="fas fa-star"></i>
				@endif
				<!-- /if user is premium then show badge -->

				@if(__ifIsset($userProfileData['isVerified'])
				and $userProfileData['isVerified'] == 1)
				<i class="fas fa-user-check text-info"></i>
				@endif
			</h4>
			<hr>
			@if((__ifIsset($userProfileData['city']) and __ifIsset($userProfileData['country_name'])))
			<i class="fas fa-map-marker-alt text-success"></i>
			<span class="mr-3"><span data-model="profileData.city"><?= $userProfileData['city'] ?></span>, <span data-model="profileData.country_name"><?= $userProfileData['country_name'] ?></span></span>
			@endif

			@if($isOwnProfile)
			<div class="float-right">
				<!-- total user likes count -->
				<i class="fas fa-heart text-danger"></i> <span id="lwTotalUserLikes" class="mr-3">
					<?= __trn('__totalUserLike__ like', '__totalUserLike__ likes', $totalUserLike, [
						'__totalUserLike__' => $totalUserLike
					]) ?></span>
				<!-- /total user likes count -->

				<!-- total user visitors count -->
				<i class="fas fa-eye text-warning"></i> <?= __trn('__totalVisitors__ view', '__totalVisitors__ views', $totalVisitors, [
															'__totalVisitors__' => $totalVisitors
														]) ?>
				<!-- /total user visitors count -->
			</div>
			@endif
		</div>
	</div>
	<!-- User Profile and Cover photo -->
	<div class="card mb-4 lw-profile-image-card-container">
		<div class="card-body">
			@if($isOwnProfile)
			<span class="lw-profile-edit-button-container">
				<a class="lw-icon-btn" href role="button" id="lwEditProfileAndCoverPhoto">
					<i class="fa fa-pencil-alt"></i>
				</a>
				<a class="lw-icon-btn" href role="button" id="lwCloseProfileAndCoverBlock" style="display: none;">
					<i class="fa fa-times"></i>
				</a>
			</span>
			@endif
            <div class="mt-4 d-none d-md-flex lw-profile-side-block-container">
                <!-- profile related -->
                @if(!$isOwnProfile)
                <div class="card mt-4 lw-profile-side-block">
                    <div class="card-body text-center">
                        <!-- Like and dislike buttons -->
                        <div class="lw-like-dislike-box">
                            <!-- like button -->
                            <a href data-action="<?= route('user.write.like_dislike', ['toUserUid' => $userData['userUId'], 'like' => 1]) ?>" data-method="post" data-callback="onLikeCallback" title="{{ __tr('Like') }}" class="lw-ajax-link-action" id="lwLikeBtn">
                                <span class="lw-animated-heart lw-animated-like-heart <?= (isset($userLikeData['like']) and $userLikeData['like'] == 1) ? 'lw-is-active' : '' ?>"></span>
                            </a>
                            <span data-model="userLikeStatus"><?= (isset($userLikeData['like']) and $userLikeData['like'] == 1) ? __tr('Liked') : __tr('Like')  ?>
                            </span>
                            <!-- /like button -->
                        </div>
                        <div class="lw-like-dislike-box mb-5">
                            <!-- dislike button -->
                            <a href data-action="<?= route('user.write.like_dislike', ['toUserUid' => $userData['userUId'], 'like' => 0]) ?>" data-method="post" data-callback="onLikeCallback" title="{{ __tr('Dislike') }}" class="lw-ajax-link-action" id="lwDislikeBtn">
                                <span class="lw-animated-heart lw-animated-broken-heart <?= (isset($userLikeData['like']) and $userLikeData['like'] == 0) ? 'lw-is-active' : '' ?>"></span>
                            </a>
                            <span data-model="userDislikeStatus"><?= (isset($userLikeData['like']) and $userLikeData['like'] == 0) ? __tr('Disliked') : __tr('Dislike')  ?>
                            </span>
                            <!-- /dislike button -->
                        </div>
                        <div class="mt-5">
                            <!-- message button -->
                            <a title="{{ __tr('Send Message or Gift') }}" class=" btn-link btn mb-3" onclick="getChatMessenger('<?= route('user.read.individual_conversation', ['specificUserId' => $userData['userId']]) ?>')" href id="lwMessageChatButton" data-chat-loaded="false" data-toggle="modal" data-target="#messengerDialog"><i class="far fa-comments fa-3x"></i>
                                <br> <?= __tr('Message') ?></a>
                            <!-- send gift button -->
                            <a href title="<?= __tr('Send Gift') ?>" data-toggle="modal" data-target="#lwSendGiftDialog" class="btn-link btn"><i class="fa fa-gift fa-3x" aria-hidden="true"></i>
                                <br> <?= __tr('Gift') ?>
                            </a>
                            <!-- /send gift button -->
                        </div>
                    </div>
                </div>
                @endif
            </div>
			<div class="row" id="lwProfileAndCoverStaticBlock">
                @if($isPremiumUser)
                <span class="lw-premium-badge lw-on-cover-image" title="<?= __tr('You are a Premium User') ?>"></span>
                @endif
				<div class="col-lg-12">
					<div class="card mb-3 lw-profile-image-card-container">
						<img class="lw-profile-thumbnail lw-photoswipe-gallery-img lw-lazy-img" id="lwProfilePictureStaticImage" data-src="<?= imageOrNoImageAvailable($userData['profilePicture']) ?>">
						<img class="lw-cover-picture card-img-top lw-photoswipe-gallery-img lw-lazy-img" id="lwCoverPhotoStaticImage" data-img-index="1" data-src="<?= imageOrNoImageAvailable($userData['coverPicture']) ?>">
					</div>
				</div>
			</div>
			@if($isOwnProfile)
			<div class="row" id="lwProfileAndCoverEditBlock" style="display: none;">
				<div class="col-lg-3">
					<input type="file" name="filepond" class="filepond lw-file-uploader" id="lwFileUploader" data-remove-media="true" data-instant-upload="true" data-action="<?= route('user.upload_profile_image') ?>" data-label-idle="<?= __tr("Drag & Drop your picture or __browseAction__", [
																																																												'__browseAction__' => "<span class='filepond--label-action'>" . __tr('Browse') . "</span>"
																																																											]) ?>" data-image-preview-height="170" data-image-crop-aspect-ratio="1:1" data-style-panel-layout="compact circle" data-style-load-indicator-position="center bottom" data-style-progress-indicator-position="right bottom" data-style-button-remove-item-position="left bottom" data-style-button-process-item-position="right bottom" data-callback="afterUploadedProfilePicture">
				</div>
				<div class="col-lg-9">
					<input type="file" name="filepond" class="filepond lw-file-uploader mt-5" id="lwFileUploader" data-remove-media="false" data-instant-upload="true" data-action="<?= route('user.upload_cover_image') ?>" data-callback="afterUploadedCoverPhoto" data-label-idle="<?= __tr("Drag & Drop your picture or __browseAction__", [
																																																																							'__browseAction__' => "<span class='filepond--label-action'>" . __tr('Browse') . "</span>"
																																																																						]) ?>">
				</div>
			</div>
			@endif
		</div>
	</div>
	<!-- /User Profile and Cover photo -->

	<!-- mobile view like dislike block -->
	@if(!$isOwnProfile)
	<div class="mb-4 d-block d-md-none">
		<!-- profile related -->
		<div class="card">
			<div class="card-header">
				<?= __tr('Like Dislike') ?>
			</div>
			<div class="card-body">
				<!-- Like and dislike buttons -->
				@if(!$isOwnProfile)
				<div class="lw-like-dislike-box">
					<!-- like button -->
					<a href data-action="<?= route('user.write.like_dislike', ['toUserUid' => $userData['userUId'], 'like' => 1]) ?>" data-method="post" data-callback="onLikeCallback" title="Like" class="lw-ajax-link-action lw-like-action-btn" id="lwLikeBtn">
						<span class="lw-animated-heart lw-animated-like-heart <?= (isset($userLikeData['like']) and $userLikeData['like'] == 1) ? 'lw-is-active' : '' ?>"></span>
					</a>
					<span data-model="userLikeStatus"><?= (isset($userLikeData['like']) and $userLikeData['like'] == 1) ? __tr('Liked') : __tr('Like')  ?>
					</span>
					<!-- /like button -->
				</div>
				<div class="lw-like-dislike-box">
					<!-- dislike button -->
					<a href data-action="<?= route('user.write.like_dislike', ['toUserUid' => $userData['userUId'], 'like' => 0]) ?>" data-method="post" data-callback="onLikeCallback" title="Dislike" class="lw-ajax-link-action lw-dislike-action-btn" id="lwDislikeBtn">
						<span class="lw-animated-heart lw-animated-broken-heart <?= (isset($userLikeData['like']) and $userLikeData['like'] == 0) ? 'lw-is-active' : '' ?>"></span>
					</a>
					<span data-model="userDislikeStatus"><?= (isset($userLikeData['like']) and $userLikeData['like'] == 0) ? __tr('Disliked') : __tr('Dislike')  ?>
					</span>
					<!-- /dislike button -->
				</div>
				@endif
			</div>
			<!-- / Like and dislike buttons -->
		</div>
		<div class="card mt-3">
			<div class="card-header">
				<?= __tr('Send Message or Gift') ?>
			</div>
			<div class="card-body text-center">
				<!-- message button -->
				<a class="mr-3 btn-link btn" onclick="getChatMessenger('<?= route('user.read.individual_conversation', ['specificUserId' => $userData['userId']]) ?>')" href id="lwMessageChatButton" data-chat-loaded="false" data-toggle="modal" data-target="#messengerDialog"><i class="far fa-comments fa-3x"></i>
					<br> <?= __tr('Message') ?></a>

				<!-- send gift button -->
				<a href title="<?= __tr('Send Gift') ?>" data-toggle="modal" data-target="#lwSendGiftDialog" class="btn-link btn"><i class="fa fa-gift fa-3x" aria-hidden="true"></i>
					<br> <?= __tr('Gift') ?>
				</a>
				<!-- /send gift button -->
			</div>
		</div>
	</div>
	@endif
	<!-- /mobile view like dislike block -->
	@if(isset($userProfileData['aboutMe']) and $userProfileData['aboutMe'])
	<div class="card mb-3">
		<div class="card-header">
            <h5><i class="fas fa-user text-primary"></i> <?= __tr('About Me') ?></h5>
		</div>
		<div class="card-body">
			<!-- About Me -->
			<div class="form-group">
				<div class="lw-inline-edit-text" data-model="profileData.aboutMe">
					<?= __ifIsset($userProfileData['aboutMe'], $userProfileData['aboutMe'], '-') ?>
				</div>
			</div>
			<!-- /About Me -->
		</div>
	</div>
	@endif
	@if(!__isEmpty($photosData) or $isOwnProfile)
	<div class="card mb-3">
		<div class="card-header">
			@if($isOwnProfile)
			<span class="float-right">
				<a class="lw-icon-btn lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" href="<?= route('user.photos_setting', ['username' => getUserAuthInfo('profile.username')]) ?>" role="button">
					<i class="fas fa-cog"></i>
				</a>
			</span>
			@endif
			<h5><i class="fas fa-images text-warning"></i> <?= __tr('Photos') ?></h5>
		</div>

		<div class="card-body">
			<div class="row text-center text-lg-left lw-horizontal-container pl-2">
				@if(!__isEmpty($photosData))
				@foreach($photosData as $key => $photo)
				<img class="lw-user-photo lw-photoswipe-gallery-img lw-lazy-img" data-img-index="<?= $key ?>" data-src="<?= imageOrNoImageAvailable($photo['image_url']) ?>">
				@endforeach
				@else
				<?= __tr('Ooops... No images found...') ?>
				@endif
			</div>
		</div>
	</div>
	@endif

	<!-- user gift data -->
	@if(!__isEmpty($userGiftData) or $isOwnProfile)
	<div class="card mb-3">
		<!-- Gift Header -->
		<div class="card-header">
			<h5><i class="fa fa-gifts" aria-hidden="true"></i> <?= __tr('Gifts') ?></h5>
		</div>
		<!-- /Gift Header -->
		<!-- Gift Card Body -->
		<div class="card-body" id="lwUserGift">
			@if(!__isEmpty($userGiftData))
			<div class="row">
				@foreach($userGiftData as $gift)
				<div class="col-sm-12 col-md-6 col-lg-2">
				<div class="lw-user-gift-container">
					<img data-src="<?= imageOrNoImageAvailable($gift['userGiftImgUrl']) ?>" class="lw-user-gift-img lw-lazy-img" />
					<small>
						<?= __tr('sent by') ?> <br>
						<a class="lw-ajax-link-action lw-action-with-url" href="<?= route('user.profile_view', ['username' => $gift['senderUserName']]) ?>"><?= $gift['fromUserName'] ?></a></small>
					@if($gift['status'] === 1)
					<i class="fas fa-mask" title="<?= __tr('This is a private gift you and only sender can see this.') ?>"></i>
					@endif
				</div>
				</div>
				@endforeach
			</div>
			<!-- show more gift button -->
			<div class="mt-3">
				<button class="btn btn-dark btn-sm btn-block" id="showMoreGiftBtn"> <i class="fa fa-chevron-down"></i> <?= __tr('Show More') ?></button>
			</div>
			<!-- /show more gift button -->

			<!-- show less gift button -->
			<div class="mt-3">
				<button class="btn btn-dark btn-sm btn-block" id="showLessGiftBtn"> <i class="fa fa-chevron-up"></i> <?= __tr('Show Less') ?></button>
			</div>
			<!-- /show less gift button -->
			@else
			<!-- info message -->
			<div class="alert alert-info">
				<?= __tr('There are no gifts.') ?>
			</div>
			<!-- / info message -->
			@endif
		</div>
		<!-- Gift Card Body -->
	</div>
	@endif
	<!-- /user gift data -->

	<!-- User Basic Information -->
	<div class="card mb-3">
		<!-- Basic information Header -->
		<div class="card-header">
			<!-- Check if its own profile -->
			@if($isOwnProfile)
			<span class="float-right">
				<a class="lw-icon-btn" href role="button" id="lwEditBasicInformation">
					<i class="fa fa-pencil-alt"></i>
				</a>
				<a class="lw-icon-btn" href role="button" id="lwCloseBasicInfoEditBlock" style="display: none;">
					<i class="fa fa-times"></i>
				</a>
			</span>
			@endif
			<!-- /Check if its own profile -->
			<h5><i class="fas fa-info-circle text-info"></i> <?= __tr('Basic Information') ?></h5>
		</div>
		<!-- /Basic information Header -->
		<!-- Basic Information content -->
		<div class="card-body">
			<!-- Static basic information container -->
			<div id="lwStaticBasicInformation">
				@if($isOwnProfile)
				<div class="form-group row">
					<!-- First Name -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="first_name"><strong><?= __tr('First Name') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="userData.first_name"><?= __ifIsset($userData['first_name'], $userData['first_name'], '-') ?></div>
					</div>
					<!-- /First Name -->
					<!-- Last Name -->
					<div class="col-sm-6">
						<label for="last_name"><strong><?= __tr('Last Name') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="userData.last_name"><?= __ifIsset($userData['last_name'], $userData['last_name'], '-') ?></div>
					</div>
					<!-- /Last Name -->
				</div>
				@endif
				<div class="form-group row">
					<!-- Gender -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="select_gender"><strong><?= __tr('Gender') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="profileData.gender_text">
							<?= __ifIsset($userProfileData['gender_text'], $userProfileData['gender_text'], '-') ?>
						</div>
					</div>
					<!-- /Gender -->
					<!-- Preferred Language -->
					<div class="col-sm-6">
						<label><strong><?= __tr('Preferred Language') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="profileData.formatted_preferred_language">
							<?= __ifIsset($userProfileData['formatted_preferred_language'], $userProfileData['formatted_preferred_language'], '-') ?>
						</div>
					</div>
					<!-- /Preferred Language -->
				</div>
				<div class="form-group row">
					<!-- Relationship Status -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label><strong><?= __tr('Relationship Status') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="profileData.formatted_relationship_status">
							<?= __ifIsset($userProfileData['formatted_relationship_status'], $userProfileData['formatted_relationship_status'], '-') ?>
						</div>
					</div>
					<!-- /Relationship Status -->
					<!-- Work Status -->
					<div class="col-sm-6">
						<label for="work_status"><strong><?= __tr('Work Status') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="profileData.formatted_work_status">
							<?= __ifIsset($userProfileData['formatted_work_status'], $userProfileData['formatted_work_status'], '-') ?>
						</div>
					</div>
					<!-- /Work Status -->
				</div>
				<div class="form-group row">
					<!-- Education -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="education"><strong><?= __tr('Education') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="profileData.formatted_education">
							<?= __ifIsset($userProfileData['formatted_education'], $userProfileData['formatted_education'], '-') ?>
						</div>
					</div>
					<!-- /Education -->
					<!-- Birthday --> 
					<div class="col-sm-6">
						<label for="birthday"><strong><?= __tr('Birthday') ?></strong></label>
						<div class="lw-inline-edit-text d-block" data-model="profileData.birthday">
							<?= __ifIsset($userProfileData['birthday'], $userProfileData['birthday'], '-') ?>
						</div>
					</div>
					<!-- /Birthday -->
				</div>
				@if(array_get($userProfileData, 'showMobileNumber'))
				<div class="form-group row">
					<!-- Mobile Number -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="mobile_number"><strong><?= __tr('Mobile Number') ?></strong></label>
						<div class="lw-inline-edit-text" data-model="profileData.mobile_number">
							<?= __ifIsset($userProfileData['mobile_number'], $userProfileData['mobile_number'], '-') ?>
						</div>
					</div>
					<!-- /Mobile Number -->
				</div>
				@endif
			</div>
			<!-- /Static basic information container -->

			@if($isOwnProfile)
			<!-- User Basic Information Form -->
			<form class="lw-ajax-form lw-form" lwSubmitOnChange method="post" data-show-message="true" action="<?= route('user.write.basic_setting') ?>" data-callback="getUserProfileData" style="display: none;" id="lwUserBasicInformationForm">
				<div class="form-group row">
					<!-- First Name -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="first_name"><?= __tr('First Name') ?></label>
						<input type="text" value="<?= $userData['first_name'] ?>" class="form-control" name="first_name" placeholder="<?= __tr('First Name') ?>" required>
					</div>
					<!-- /First Name -->
					<!-- Last Name -->
					<div class="col-sm-6">
						<label for="last_name"><?= __tr('Last Name') ?></label>
						<input type="text" value="<?= $userData['last_name'] ?>" class="form-control" name="last_name" placeholder="<?= __tr('Last Name') ?>" required>
					</div>
					<!-- /Last Name -->
				</div>
				<div class="form-group row">
					<!-- Gender -->
					<div class="col-sm-6 mb-3 mb-sm-0 mt-2">
						<label for="select_gender"><?= __tr('Gender') ?></label>
						<select name="gender" class="form-control custom-select" id="select_gender">
							<option value="" selected disabled><?= __tr('Choose your gender') ?></option>
							@foreach($genders as $genderKey => $gender)
							<option value="<?= $genderKey ?>" <?= (__ifIsset($userProfileData['gender']) and $genderKey == $userProfileData['gender']) ? 'selected' : '' ?>><?= $gender ?></option>
							@endforeach
						</select>
					</div>

					<!-- /Gender -->
					<!-- Birthday -->
					<div class="col-sm-6 mt-2">
						<label for="select_preferred_language"><?= __tr('Preferred Language') ?></label>
						<select name="preferred_language" class="form-control custom-select" id="select_preferred_language">
							<option value="" selected disabled><?= __tr('Choose your Preferred Language') ?></option>
							@foreach($preferredLanguages as $languageKey => $language)
							<option value="<?= $languageKey ?>" <?= (__ifIsset($userProfileData['preferred_language']) and $languageKey == $userProfileData['preferred_language']) ? 'selected' : '' ?>><?= $language ?></option>
							@endforeach
						</select>
					</div>
					<!-- /Preferred Language -->
				</div>
				<div class="form-group row">
					<!-- Relationship Status -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="select_relationship_status"><?= __tr('Relationship Status') ?></label>
						<select name="relationship_status" class="form-control custom-select" id="select_relationship_status">
							<option value="" selected disabled><?= __tr('Choose your Relationship Status') ?></option>
							@foreach($relationshipStatuses as $relationshipStatusKey => $relationshipStatus)
							<option value="<?= $relationshipStatusKey ?>" <?= (__ifIsset($userProfileData['relationship_status']) and $relationshipStatusKey == $userProfileData['relationship_status']) ? 'selected' : '' ?>><?= $relationshipStatus ?></option>
							@endforeach
						</select>
					</div>
					<!-- /Relationship Status -->
					<!-- Work status -->
					<div class="col-sm-6">
						<label for="select_work_status"><?= __tr('Work Status') ?></label>
						<select name="work_status" class="form-control custom-select" id="select_work_status">
							<option value="" selected disabled><?= __tr('Choose your work status') ?></option>
							@foreach($workStatuses as $workStatusKey => $workStatus)
							<option value="<?= $workStatusKey ?>" <?= (__ifIsset($userProfileData['work_status']) and $workStatusKey == $userProfileData['work_status']) ? 'selected' : '' ?>><?= $workStatus ?></option>
							@endforeach
						</select>
					</div>
					<!-- /Work status -->
				</div>
				<div class="form-group row">
					<!-- Education -->
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label for="select_education"><?= __tr('Education') ?></label>
						<select name="education" class="form-control custom-select" id="select_education">
							<option value="" selected disabled><?= __tr('Choose your education') ?></option>
							@foreach($educations as $educationKey => $educationValue)
							<option value="<?= $educationKey ?>" <?= (__ifIsset($userProfileData['education']) and $educationKey == $userProfileData['education']) ? 'selected' : '' ?>><?= $educationValue ?></option>
							@endforeach
						</select>
					</div>
					<!-- /Education -->
					<!-- Birthday -->
					<div class="col-sm-6">
						<label for="birthday"><?= __tr('Birthday') ?></label>
                        <input type="date" min="{{ getAgeDate(configItem('age_restriction.maximum'), 'max')->format('Y-m-d') }}" max="{{ getAgeDate(configItem('age_restriction.minimum'))->format('Y-m-d') }}" class="form-control d-block" name="birthday" placeholder="<?= __tr('DD-MM-YYYY') ?>" value="<?= __ifIsset($userProfileData['dob'], $userProfileData['dob']) ?>" required="true">
					</div>
					<!-- /Birthday -->
				</div>
				@if($isOwnProfile)

				<div class="form-group row">
					<!-- Mobile Number -->
                    <div class="form-group col-sm-6">
                        <label for="lwMobileNUmberField"><?= __tr('Mobile Number') ?></label>
                        <div class="input-group">
                            <select name="country_code" class="custom-select form-control lw-country-code-select" id="country_code" required>
                                <option value="">{{  __tr('Select Country Code') }}</option>
                                @foreach(getCountryPhoneCodes() as $getCountryCode)
                                <option value="<?= $getCountryCode['phone_code'] ?>" <?= ($userData['country_code'] == $getCountryCode['phone_code']) ? 'selected' : '' ?>><?= $getCountryCode['name'] ?> (0{{ $getCountryCode['phone_code'] }})</option>
                                @endforeach
                            </select>
                            <input type="number" value="{{ $userData['mobile_number'] }}" class="form-control lw-remove-spinner" placeholder="{{  __tr('Mobile Number') }}" name="mobile_number" required  minlength="8" maxlength="15">
                          </div>
                   </div>
					<!-- /Mobile Number -->
				</div>
				<!-- About Me -->
				<div class="form-group mt-5">
					<label for="about_me"><?= __tr('About Me') ?></label>
					<textarea class="form-control" name="about_me" id="about_me" rows="3" placeholder="<?= __tr('Say something about yourself.') ?>"><?= __ifIsset($userProfileData['aboutMe'], $userProfileData['aboutMe'], '') ?></textarea>
				</div>
				<!-- /About Me -->
				@endif
			</form>
			<!-- /User Basic Information Form -->
			@endif
		</div>
	</div>
	<!-- /User Basic Information -->
	<div class="card mb-3">
		<div class="card-header">
			@if($isOwnProfile)
			<span class="float-right">
				<a class="lw-icon-btn" href role="button" id="lwEditUserLocation">
					<i class="fa fa-pencil-alt"></i>
				</a>
				<a class="lw-icon-btn" href role="button" id="lwCloseLocationBlock" style="display: none;">
					<i class="fa fa-times"></i>
				</a>
			</span>
			@endif
			<h5><i class="fas fa-map-marker-alt"></i> <?= __tr('Location') ?></h5>
		</div>
		<div class="card-body">
			<div id="lwUserStaticLocation">
			@if(getStoreSettings('display_google_map'))
				<div class="gmap_canvas"><iframe height="300" id="gmap_canvas" src="https://maps.google.com/maps/place?q=<?= $latitude ?>,<?= $longitude ?>&output=embed&language={{ app()->getLocale() }}" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
				</div>
			@else
			<div id="staticMapId"></div>
			@endif
			</div>
            @if(getStoreSettings('allow_google_map') or getStoreSettings('use_static_city_data'))
			<div id="lwUserEditableLocation" style="display: none;">
			@if(getStoreSettings('use_static_city_data'))
				<div class="form-group">
					<label for="selectLocationCity"><?= __tr('Location') ?></label>
					<input type="text" id="selectLocationCity" class="form-control" placeholder="<?= __tr('Enter a location') ?>">
				</div>
				@else
				<div class="form-group">
					<label for="address_address"><?= __tr('Location') ?></label>
					<input type="text" id="address-input" name="address_address" class="form-control map-input" placeholder="<?= __tr('Enter a location') ?>">

					<!-- show select location on map error -->
					<div class="alert alert-danger mt-2 alert-dismissible" style="display: none" id="lwShowLocationErrorMessage">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<span data-model="locationErrorMessage"></span>
					</div>
					<!-- /show select location on map error -->

					<input type="hidden" name="address_latitude" data-model="profileData.latitude" id="address-latitude" value="<?= $latitude ?>" />
					<input type="hidden" name="address_longitude" data-model="profileData.longitude" id="address-longitude" value="<?= $longitude ?>" />
				</div>
				<div id="address-map-container" style="width:100%;height:400px; ">
					<div style="width: 100%; height: 100%" id="address-map"></div>
				</div>
			</div>
			@endif
			@else
			<!-- info message -->
			<div class="alert alert-info">
				<?= __tr('Something went wrong with Google Api Key, please contact to system administrator.') ?>
			</div>
			<!-- / info message -->
			@endif
		</div>
	</div>

	<!-- User Specifications -->
	@if(!__isEmpty($userSpecificationData))
	@foreach($userSpecificationData as $specificationKey => $specifications)
	<div class="card mb-3">
		<!-- User Specification Header -->
		<div class="card-header">
			<!-- Check if its own profile -->
			@if($isOwnProfile)
			<span class="float-right">
				<a class="lw-icon-btn" href role="button" id="lwEdit<?= $specificationKey ?>" onclick="showHideSpecificationUser('<?= $specificationKey ?>', event)">
					<i class="fa fa-pencil-alt"></i>
				</a>
				<a class="lw-icon-btn" href role="button" id="lwClose<?= $specificationKey ?>Block" onclick="showHideSpecificationUser('<?= $specificationKey ?>', event)" style="display: none;">
					<i class="fa fa-times"></i>
				</a>
			</span>
			@endif
			<!-- /Check if its own profile -->
			<h5><?= $specifications['icon'] ?> <?= $specifications['title'] ?></h5>
		</div>
		<!-- /User Specification Header -->
		<div class="card-body">
			<!-- User Specification static container -->
			<div id="lw<?= $specificationKey ?>StaticContainer">
				@foreach(collect($specifications['items'])->chunk(2) as $specKey => $specification)
				<div class="form-group row">
					@foreach($specification as $itemKey => $item)
					<div class="col-sm-6 mb-3 mb-sm-0">
						<label><strong><?= $item['label'] ?></strong></label>
						<div class="lw-inline-edit-text" data-model="specificationData.<?= $item['name'] ?>">
							<?= __tr($item['value'],escapeInputString:false) ?>
						</div>
					</div>
					@endforeach
				</div>
				@endforeach
			</div>
			<!-- /User Specification static container -->
			@if($isOwnProfile)
			<!-- User Specification Form -->
			<form class="lw-ajax-form lw-form" method="post" lwSubmitOnChange action="<?= route('user.write.profile_setting') ?>" data-callback="getUserProfileData" id="lwUser<?= $specificationKey ?>Form" style="display: none;">
				@foreach(collect($specifications['items'])->chunk(2) as $specification)
				<div class="form-group row">
					@foreach($specification as $itemKey => $item)
					<div class="col-sm-6 mb-3 mb-sm-0">
						@if($item['input_type'] == 'select')
						<label for="<?= $item['name'] ?>"><?= $item['label'] ?></label>
						<select name="<?= $item['name'] ?>" class="form-control custom-select">
							<option value="" selected disabled><?= __tr('Choose __label__', [
																	'__label__' => $item['label']
																]) ?></option>
							@if(!__isEmpty($item['options']))
							@foreach($item['options'] as $optionKey => $option)
							<option value="<?= $optionKey ?>" <?= $item['selected_options'] == $optionKey ? 'selected' : '' ?>>
								<?= __tr($option,escapeInputString:false) ?>
							</option>
							@endforeach
							@endif
						</select>
						@elseif($item['input_type'] == 'textbox')
						<label for="<?= $item['name'] ?>"><?= $item['label'] ?></label>
						<input type="text" id="<?= $item['name'] ?>" name="<?= $item['name'] ?>" class="form-control" value="<?= $item['selected_options'] ?>">
						@endif
					</div>
					@endforeach
				</div>
				@endforeach
			</form>
			<!-- /User Specification Form -->
			@endif
		</div>
	</div>
	@endforeach
	@endif
	<!-- /User Specifications -->


	<!-- user report Modal-->
	<div class="modal fade" id="lwReportUserDialog" tabindex="-1" role="dialog" aria-labelledby="userReportModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-md" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="userReportModalLabel"><?= __tr('Abuse Report to __username__', [
																			'__username__' => $userData['fullName']
																		]) ?></h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<form class="lw-ajax-form lw-form" id="lwReportUserForm" method="post" data-callback="userReportCallback" action="<?= route('user.write.report_user', ['sendUserUId' => $userData['userUId']]) ?>">
					<div class="modal-body">
						<!-- reason input field -->
						<div class="form-group">
							<label for="lwUserReportReason"><?= __tr('Reason') ?></label>
							<textarea class="form-control" rows="3" id="lwUserReportReason" name="report_reason" required></textarea>
						</div>
						<!-- / reason input field -->
					</div>

					<!-- modal footer -->
					<div class="modal-footer mt-3">
						<button class="btn btn-light btn-sm" id="lwCloseUserReportDialog"><?= __tr('Cancel') ?></button>
						<button type="submit" class="btn btn-primary btn-sm lw-ajax-form-submit-action btn-user lw-btn-block-mobile"><?= __tr('Report') ?></button>
					</div>
				</form>
				<!-- modal footer -->
			</div>
		</div>
	</div>
	<!-- /user report Modal-->

	<!-- send gift Modal-->
	<div class="modal fade" id="lwSendGiftDialog" tabindex="-1" role="dialog" aria-labelledby="sendGiftModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-md" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<?php $totalAvailableCredits = totalUserCredits() ?>
					<h5 class="modal-title" id="sendGiftModalLabel"><?= __tr('Send Gift') ?> <small class="text-muted"><?= __tr('(Credits Available:  __availableCredits__)', [
																															'__availableCredits__' => $totalAvailableCredits
																														]) ?></small></h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				@if(isset($giftListData) and !__isEmpty($giftListData))

				<!-- insufficient balance error message -->
				<div class="alert alert-info" id="lwGiftModalErrorText" style="display: none">
					<?= __tr('Your credit balance is too low, please') ?>
					<a href="<?= route('user.credit_wallet.read.view') ?>"><?= __tr('purchase credits') ?></a>
				</div>
				<!-- / insufficient balance error message -->

				<form class="lw-ajax-form lw-form" id="lwSendGiftForm" method="post" data-callback="sendGiftCallback" action="<?= route('user.write.send_gift', ['sendUserUId' => $userData['userUId']]) ?>">
					<div class="modal-body">
						<div class="btn-group-toggle" data-toggle="buttons">
							@foreach($giftListData as $key => $gift)
							<span class="btn lw-group-radio-option-img" id="lwSendGiftRadioBtn_<?= $gift['_uid'] ?>">
								<input type="radio" value="<?= $gift['_uid'] ?>" name="selected_gift" />
								<span>
									<img class="lw-lazy-img" data-src="<?= imageOrNoImageAvailable($gift['gift_image_url']) ?>" /><br>
									<?= $gift['formattedPrice'] ?>
								</span>
							</span>
							@endforeach
						</div>

						<!-- select private / public -->
						<div class="custom-control custom-checkbox custom-control-inline mt-3">
							<input type="checkbox" class="custom-control-input" id="isPrivateCheck" name="isPrivateGift">
							<label class="custom-control-label" for="isPrivateCheck"><?= __tr('Private')  ?></label>
						</div>
						<!-- /select private / public -->
					</div>
					<!-- modal footer -->
					<div class="modal-footer mt-3">
						<button class="btn btn-light btn-sm" id="lwCloseSendGiftDialog"><?= __tr('Cancel') ?></button>
						<button type="submit" class="btn btn-primary btn-sm lw-ajax-form-submit-action btn-user lw-btn-block-mobile"><?= __tr('Send') ?></button>
					</div>
					<!-- modal footer -->
				</form>
				@else
				<!-- info message -->
				<div class="alert alert-info">
					<?= __tr('There are no gifts') ?>
				</div>
				<!-- / info message -->
				@endif
			</div>
		</div>
	</div>
	<!-- /send gift Modal-->

	<!-- User block Confirmation text html -->
	<div id="lwBlockUserConfirmationText" style="display: none;">
		<h3><?= __tr('Are You Sure!') ?></h3>
		<strong><?= __tr('You want to block this user.') ?></strong>
	</div>
	<!-- /User block Confirmation text html -->
@endif
<!-- /if user block then don't show profile page content -->

@lwPush('appScripts')
@if(getStoreSettings('allow_google_map'))
<script src="https://maps.googleapis.com/maps/api/js?key=<?= getStoreSettings('google_map_key') ?>&libraries=places&callback=initialize&language={{ app()->getLocale() }}" async defer></script>
@endif
<script>
	//start show more gift item if user gift length is greater then 10
	var userGiftLength = $("#lwUserGift, .lw-user-gift-container").length;

	$("#showMoreGiftBtn").hide();
	$("#showLessGiftBtn").hide();
	//check gift length is greater than 10
	if (userGiftLength > 8) {
		$("#showMoreGiftBtn").show();
		$('.lw-user-gift-container').hide();
		$('.lw-user-gift-container:lt(8)').show();

		//on click show more gift show all remaining gifts
		$("#showMoreGiftBtn").on('click', function() {
			$('.lw-user-gift-container:lt(' + userGiftLength + ')').show();
			$("#showMoreGiftBtn").hide();
			$("#showLessGiftBtn").show();
		});

		//on click show less gift show only 10 gifts
		$("#showLessGiftBtn").on('click', function() {
			$('.lw-user-gift-container').hide();
			$('.lw-user-gift-container:lt(8)').show();
			$("#showMoreGiftBtn").show();
			$("#showLessGiftBtn").hide();
		});
	}
	//end show more gift item if user gift length is greater then 10


	// Get user profile data
	function getUserProfileData(response) {
		// If successfully stored data
		if (response.reaction == 1) {
			__DataRequest.get("<?= route('user.get_profile_data', ['username' => getUserAuthInfo('profile.username')]) ?>", {}, function(responseData) {
				var requestData = responseData.data;
				var specificationUpdateData = [];
				_.forEach(requestData.userSpecificationData, function(specification) {
					_.forEach(specification['items'], function(item) {
						specificationUpdateData[item.name] = item.value;
					});
				});
				__DataRequest.updateModels('userData', requestData.userData);
				__DataRequest.updateModels('profileData', requestData.userProfileData);
				__DataRequest.updateModels('specificationData', specificationUpdateData);
			});
		}
	}

	/**************** User Like Dislike Fetch and Callback Block Start ******************/
	//add disabled anchor tag class on click
	$(".lw-like-action-btn, .lw-dislike-action-btn").on('click', function() {
		$('.lw-like-dislike-box').addClass("lw-disable-anchor-tag");
	});
	//on like Callback function
	function onLikeCallback(response) {
		var requestData = response.data;
		//check reaction code is 1 and status created or updated and like status is 1
		if (response.reaction == 1 && requestData.likeStatus == 1 && (requestData.status == "created" || requestData.status == 'updated')) {
			__DataRequest.updateModels({
				'userLikeStatus': '<?= __tr('Liked') ?>', //user liked status
				'userDislikeStatus': '<?= __tr('Dislike') ?>', //user dislike status
			});
			//add class
			$(".lw-animated-like-heart").toggleClass("lw-is-active");
			//check if updated then remove class in dislike heart
			if (requestData.status == 'updated') {
				$(".lw-animated-broken-heart").toggleClass("lw-is-active");
			}
		}
		//check reaction code is 1 and status created or updated and like status is 2
		if (response.reaction == 1 && requestData.likeStatus == 2 && (requestData.status == "created" || requestData.status == 'updated')) {
			__DataRequest.updateModels({
				'userLikeStatus': '<?= __tr('Like') ?>', //user like status
				'userDislikeStatus': '<?= __tr('Disliked') ?>', //user disliked status
			});
			//add class
			$(".lw-animated-broken-heart").toggleClass("lw-is-active");
			//check if updated then remove class in like heart
			if (requestData.status == 'updated') {
				$(".lw-animated-like-heart").toggleClass("lw-is-active");
			}
		}
		//check reaction code is 1 and status deleted and like status is 1
		if (response.reaction == 1 && requestData.likeStatus == 1 && requestData.status == "deleted") {
			__DataRequest.updateModels({
				'userLikeStatus': '<?= __tr('Like') ?>', //user like status
			});
			$(".lw-animated-like-heart").toggleClass("lw-is-active");
		}
		//check reaction code is 1 and status deleted and like status is 2
		if (response.reaction == 1 && requestData.likeStatus == 2 && requestData.status == "deleted") {
			__DataRequest.updateModels({
				'userDislikeStatus': '<?= __tr('Dislike') ?>', //user like status
			});
			$(".lw-animated-broken-heart").toggleClass("lw-is-active");
		}
		//remove disabled anchor tag class
		_.delay(function() {
			$('.lw-like-dislike-box').removeClass("lw-disable-anchor-tag");
		}, 1000);
	}
	/**************** User Like Dislike Fetch and Callback Block End ******************/


	//send gift callback
	function sendGiftCallback(response) {
		//check success reaction is 1
		if (response.reaction == 1) {
			var requestData = response.data;
			//form reset after success
			$("#lwSendGiftForm").trigger("reset");
			//remove active class after success on select gift radio option
			$("#lwSendGiftRadioBtn_" + requestData.giftUid).removeClass('active');
			//close dialog after success
			$('#lwSendGiftDialog').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            //updated credit wallet amt
            if (_.has(requestData, 'creditsRemaining')) {
                $("#lwTotalCreditWalletAmt").html(requestData.creditsRemaining)
            }

			//if error type is insufficient balance then show error message
		} else if (response.data['errorType'] == 'insufficient_balance') {
			//show error div
			$("#lwGiftModalErrorText").show();
		} else {
			//hide error div
			$("#lwGiftModalErrorText").hide();
		}
	}

	//close Send Gift Dialog
	$("#lwCloseSendGiftDialog").on('click', function(e) {
		e.preventDefault();
		//form reset after success
		$("#lwSendGiftForm").trigger("reset");
		//close dialog after success
		$('#lwSendGiftDialog').modal('hide');
	});

	//user report callback
	function userReportCallback(response) {
		//check success reaction is 1
		if (response.reaction == 1) {
			var requestData = response.data;
			//form reset after success
			$("#lwReportUserForm").trigger("reset");
			//close dialog after success
			$('#lwReportUserDialog').modal('hide');
		}
	}

	//close User Report Dialog
	$("#lwCloseUserReportDialog").on('click', function(e) {
		e.preventDefault();
		//form reset after success
		$("#lwReportUserForm").trigger("reset");
		//close dialog after success
		$('#lwReportUserDialog').modal('hide');
	});

	//block user confirmation
	$("#lwBlockUserBtn").on('click', function(e) {
		var confirmText = $('#lwBlockUserConfirmationText');
		//show confirmation 
		showConfirmation(confirmText, function() {
			var requestUrl = '<?= route('user.write.block_user') ?>',
				formData = {
					'block_user_id': '<?= $userData['userUId'] ?>',
				};
			// post ajax request
			__DataRequest.post(requestUrl, formData, function(response) {
				if (response.reaction == 1) {
				}
			});
		}, {
            background: '#333',
            popup: 'dark-theme',
        });
	});

	// Click on edit / close button 
	$('#lwEditBasicInformation, #lwCloseBasicInfoEditBlock').click(function(e) {
		e.preventDefault();
		showHideBasicInfoContainer();
	});
	// Show / Hide basic information container
	function showHideBasicInfoContainer() {
		$('#lwUserBasicInformationForm').toggle();
		$('#lwStaticBasicInformation').toggle();
		$('#lwCloseBasicInfoEditBlock').toggle();
		$('#lwEditBasicInformation').toggle();
	}
	// Show hide specification user settings
	function showHideSpecificationUser(formId, event) {
		event.preventDefault();
		$('#lwEdit' + formId).toggle();
		$('#lw' + formId + 'StaticContainer').toggle();
		$('#lwUser' + formId + 'Form').toggle();
		$('#lwClose' + formId + 'Block').toggle();
	}
	// Click on profile and cover container edit / close button 
	$('#lwEditProfileAndCoverPhoto, #lwCloseProfileAndCoverBlock').click(function(e) {
		e.preventDefault();
		showHideProfileAndCoverPhotoContainer();
	});
	// Hide / show profile and cover photo container
	function showHideProfileAndCoverPhotoContainer() {
		$('#lwProfileAndCoverStaticBlock').toggle();
		$('#lwProfileAndCoverEditBlock').toggle();
		$('#lwEditProfileAndCoverPhoto').toggle();
		$('#lwCloseProfileAndCoverBlock').toggle();
	}
	// After successfully upload profile picture
	function afterUploadedProfilePicture(responseData) {
		$('#lwProfilePictureStaticImage, .lw-profile-thumbnail').attr('src', responseData.data.image_url);
	}
	// After successfully upload Cover photo
	function afterUploadedCoverPhoto(responseData) {
		$('#lwCoverPhotoStaticImage').attr('src', responseData.data.image_url);
	}
</script>
<script>
	// Click on edit / close button 
	$('#lwEditUserLocation, #lwCloseLocationBlock').click(function(e) {
		e.preventDefault();
		showHideLocationContainer();
	});
	// Show hide location container
	function showHideLocationContainer() {
		$('#lwUserStaticLocation').toggle();
		$('#lwUserEditableLocation').toggle();
		$('#lwEditUserLocation').toggle();
		$('#lwCloseLocationBlock').toggle();
	}

	function initialize() {
		@if(getStoreSettings('allow_google_map'))
		$('form').on('keyup keypress', function(e) {
			var keyCode = e.keyCode || e.which;
			if (keyCode === 13) {
				e.preventDefault();
				return false;
			}
		});
		const locationInputs = document.getElementsByClassName("map-input");

		const autocompletes = [];
		const geocoder = new google.maps.Geocoder;
		for (let i = 0; i < locationInputs.length; i++) {

			const input = locationInputs[i];
			const fieldKey = input.id.replace("-input", "");
			const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';

			const latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || -33.8688;
			const longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 151.2195;

			const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
				center: {
					lat: latitude,
					lng: longitude
				},
				zoom: 13
			});
			const marker = new google.maps.Marker({
				map: map,
				position: {
					lat: latitude,
					lng: longitude
				},
			});

			marker.setVisible(isEdit);

			const autocomplete = new google.maps.places.Autocomplete(input);
			autocomplete.key = fieldKey;
			autocompletes.push({
				input: input,
				map: map,
				marker: marker,
				autocomplete: autocomplete
			});
		}

		for (let i = 0; i < autocompletes.length; i++) {
			const input = autocompletes[i].input;
			const autocomplete = autocompletes[i].autocomplete;
			const map = autocompletes[i].map;
			const marker = autocompletes[i].marker;

			google.maps.event.addListener(autocomplete, 'place_changed', function() {
				marker.setVisible(false);
				const place = autocomplete.getPlace();

				geocoder.geocode({
					'placeId': place.place_id
				}, function(results, status) {
					if (status === google.maps.GeocoderStatus.OK) {
						const lat = results[0].geometry.location.lat();
						const lng = results[0].geometry.location.lng();
						setLocationCoordinates(autocomplete.key, lat, lng, place);
					}
				});

				if (!place.geometry) {
					window.alert("No details available for input: '" + place.name + "'");
					input.value = "";
					return;
				}

				if (place.geometry.viewport) {
					map.fitBounds(place.geometry.viewport);
				} else {
					map.setCenter(place.geometry.location);
					map.setZoom(17);
				}
				marker.setPosition(place.geometry.location);
				marker.setVisible(true);

			});
		}
		@endif
	}

	function setLocationCoordinates(key, lat, lng, placeData) {
		__DataRequest.post("<?= route('user.write.location_data') ?>", {
			'latitude': lat,
			'longitude': lng,
			'placeData': placeData.address_components
		}, function(responseData) {
			var requestData = responseData.data;
			//check reaction code is not 1
			if (responseData.reaction != 1) {
				$("#lwShowLocationErrorMessage").show();
				__DataRequest.updateModels({
					'locationErrorMessage': requestData.message
				});
				return false;
			}
			//check reaction code is 1
			if (responseData.reaction == 1) {
				$("#lwShowLocationErrorMessage").hide();
				showHideLocationContainer();
				__DataRequest.updateModels('profileData', {
					city: requestData.city,
					country_name: requestData.country_name,
					latitude: lat,
					longitude: lng
				});
			}

			var mapSrc = "https://maps.google.com/maps/place?q=" + lat + "," + lng + "&output=embed&language={{ app()->getLocale() }}";
			$('#gmap_canvas').attr('src', mapSrc)
		});
	};
	@if(!getStoreSettings('allow_google_map') and getStoreSettings('use_static_city_data'))
	var $selectLocationCity = $('#selectLocationCity').selectize({
		// plugins: ['restore_on_backspace'],
		valueField: 'id',
		labelField: 'cities_full_name',
		searchField: [
			'cities_full_name'
		],
		// options: [],
		create: false,
		// loadThrottle: 2000,
		maxItems: 1,
		render: {
			option: function(item, escape) {
				return '<div><span class="title"><span class="name">' + escape(item.cities_full_name) + '</span></span></div>';
			}
		},
		load: function(query, callback) {
			if (!query.length || (query.length < 2)) {
				return callback([]);
			} else {
				__DataRequest.post("<?= route('user.read.search_static_cities') ?>", {
					'search_query': query
				}, function(responseData) {
					callback(responseData.data.search_result);
				});
			}
		},
		onChange: function(value) {
			if (!value.length) {
				return;
			};
			__DataRequest.post("<?= route('user.write.store_city') ?>", {
				'selected_city_id': value
			}, function(responseData) {
				if (responseData.reaction == 1) {
					__Utils.viewReload();
				}
			});
		}
	});

	var selectLocationCityControl = $selectLocationCity[0].selectize;
	selectLocationCityControl.clear(true);
	selectLocationCityControl.clearOptions(true);
    @endif
    @if(!getStoreSettings('display_google_map'))
	// leaflet map
	var leafletMap = L.map('staticMapId').setView(["<?= $latitude ?>", "<?= $longitude ?>"], 13);
	L.tileLayer(
		'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, '
		}
	).addTo(leafletMap);
    var leafLetOptions = {};
    @if(request()->ajax())
        leafLetOptions = {
            icon : L.icon({
            iconUrl: "{{ asset('dist/css/images/marker-icon.png') }}"
        })
    };
    @endif
	// add marker
	L.marker(["<?= $latitude ?>", "<?= $longitude ?>"],leafLetOptions).addTo(leafletMap);
	@endif
</script>
@lwPushEnd