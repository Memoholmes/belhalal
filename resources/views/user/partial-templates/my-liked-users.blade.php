@foreach($usersData as $user)
<div class="col mb-4">
	<div class="card text-center lw-user-thumbnail-block <?= (isset($user['isPremiumUser']) and $user['isPremiumUser'] == true) ? 'lw-has-premium-badge' : '' ?>">
		<!-- show user online, idle or offline status -->
		@if($user['userOnlineStatus'])
		<div class="pt-2">
			@if($user['userOnlineStatus'] == 1)
			<span class="lw-dot lw-dot-success" title="Online"></span>
			@elseif($user['userOnlineStatus'] == 2)
			<span class="lw-dot lw-dot-warning" title="Idle"></span>
			@elseif($user['userOnlineStatus'] == 3)
			<span class="lw-dot lw-dot-danger" title="Offline"></span>
			@endif
		</div>
		@endif
		<!-- /show user online, idle or offline status -->
		<a class="lw-ajax-link-action lw-action-with-url" href="<?= route('user.profile_view', ['username' => $user['username']]) ?>">
			<img data-src="<?= imageOrNoImageAvailable($user['userImageUrl']) ?>" class="lw-user-thumbnail lw-lazy-img" />
		</a>
		<div class="card-title">
			<h5>
				<a class="text-secondary" href="<?= route('user.profile_view', ['username' => $user['username']]) ?>">
					<?= $user['userFullName'] ?>
				</a>
				<?= $user['detailString'] ?> <br>
				@if($user['countryName'])
				<?= $user['countryName'] ?>
				@endif
			</h5>
			<span><?= $user['updated_at'] ?></span>
		</div>
	</div>
</div>
@endforeach


@if(!__isEmpty($nextPageUrl))
<div id="lwNextPageLink" class="col-sm-12 col-md-12 col-lg-12">
	<a href="<?= $nextPageUrl ?>" class="btn btn-dark btn-block lw-ajax-link-action" data-method="get" data-callback="loadNextLikedUsers"><?= __tr('Load more') ?></a>
</div>
@else
<div class="col-sm-12 col-md-12 col-lg-12 alert alert-dark text-center bg-dark text-secondary border-0 mt-5"><?= __tr('Looks like you reached the end.') ?></div>
@endIf