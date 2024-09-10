<!-- user encounter main container -->
@if(getFeatureSettings('user_encounter'))
@if(!__isEmpty($randomUserData))
<!-- random user block -->
<div class="lw-random-user-block">
	@if($randomUserData['isPremiumUser'])
	<span class="lw-premium-badge" title="<?= __tr('Premium User') ?>"></span>
	@endif
	<!-- user name -->
	<div class="lw-user-text">
		<a class="btn btn-link lw-user-text-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.profile_view', ['username' => $randomUserData['username']]) ?>">
			<?= $randomUserData['userFullName'] ?>@if(isset($randomUserData['userAge'])),@endif
		</a>
		<span class="lw-user-text-meta">
			@if($randomUserData['userAge'])
			<?= $randomUserData['userAge'] ?>
			@endif
			@if($randomUserData['countryName'])
			<?= $randomUserData['countryName'] ?>
			@endif
			@if($randomUserData['gender'])
			<?= $randomUserData['gender'] ?>
			@endif
		</span>
		<!-- show user online, idle or offline status -->
		@if($randomUserData['userOnlineStatus'])
		@if($randomUserData['userOnlineStatus'] == 1)
		<span class="lw-dot lw-dot-success float-right" title="{{ __tr('Online') }}"></span>
		@elseif($randomUserData['userOnlineStatus'] == 2)
		<span class="lw-dot lw-dot-warning float-right" title="{{ __tr('Idle') }}"></span>
		@elseif($randomUserData['userOnlineStatus'] == 3)
		<span class="lw-dot lw-dot-danger float-right" title="{{ __tr('Offline') }}"></span>
		@endif
		@endif
		<!-- /show user online, idle or offline status -->
	</div>
	<!-- /user name -->
	<div class="lw-profile-image-card-container lw-encounter-page">
		<!-- user image -->
		<img data-src="<?= $randomUserData['userImageUrl'] ?>" class="lw-lazy-img lw-profile-thumbnail">
		<!-- /user image -->
		<!-- user image -->
		<img data-src="<?= $randomUserData['userCoverUrl'] ?>" class="lw-lazy-img lw-cover-picture">
		<!-- /user image -->
	</div>
	<!-- action buttons -->
	<div class="lw-user-action-btn">
		<!-- like btn -->
		<a href data-action="<?= route('user.write.encounter.like_dislike', ['toUserUid' => $randomUserData['_uid'], 'like' => 1]) ?>" data-callback="onLikeDisLikeCallback" data-method="post" class="lw-ajax-link-action lw-like-dislike-btn mr-3" title="Like" id="lwLikeBtn"><i class="fa fa-heart text-primary"></i></a>
		<!-- /like btn -->

		<!-- skip btn -->
		<a href data-action="<?= route('user.write.encounter.skip_user', ['toUserUid' => $randomUserData['_uid']]) ?>" data-method="post" class="lw-ajax-link-action lw-like-dislike-btn lw-skip-btn mr-3 " data-callback="onEncounterUserCallback" id="lwSkipBtn"><i class="fas fa-chevron-right text-muted"></i></a>
		<!-- /skip btn -->

		<!-- Dislike btn -->
		<a href data-action="<?= route('user.write.encounter.like_dislike', ['toUserUid' => $randomUserData['_uid'], 'like' => 0]) ?>" data-callback="onLikeDisLikeCallback" data-method="post" class="lw-ajax-link-action lw-like-dislike-btn mr-3" title="Dislike" id="lwDislikeBtn"><i class="fa fa-heart-broken text-danger"></i></a>
		<!-- /Dislike btn -->
	</div>
	<!-- /action buttons -->
</div>
<!-- /random user block -->
@else
<!-- info message -->
<div class="alert alert-info">
	<?= __tr('Your daily limit for encounters may exceed or there are no users to show.') ?>
</div>
<!-- / info message -->
@endif
@else
<!-- info message -->
<div class="alert alert-info">
	<?= __tr('This is a premium feature, to view encounter you need to buy premium plan first.') ?>
</div>
<!-- / info message -->
@endif
<!-- /user encounter main container -->