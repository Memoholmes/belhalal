@if(!__isEmpty($filterData))
@foreach($filterData as $filter)
<div class="col mb-4">
	<div class="card text-center lw-user-thumbnail-block <?= (isset($filter['isPremiumUser']) and $filter['isPremiumUser'] == true) ? 'lw-has-premium-badge' : '' ?>">
		<!-- show user online, idle or offline status -->
		@if($filter['userOnlineStatus'])
		<div class="pt-2">
			@if($filter['userOnlineStatus'] == 1)
			<span class="lw-dot lw-dot-success" title="Online"></span>
			@elseif($filter['userOnlineStatus'] == 2)
			<span class="lw-dot lw-dot-warning" title="Idle"></span>
			@elseif($filter['userOnlineStatus'] == 3)
			<span class="lw-dot lw-dot-danger" title="Offline"></span>
			@endif
		</div>
		@endif
		<!-- /show user online, idle or offline status -->
		<a class="lw-ajax-link-action lw-action-with-url" href="<?= route('user.profile_view', ['username' => $filter['username']]) ?>">
			<img data-src="<?= imageOrNoImageAvailable($filter['profileImage']) ?>" class="lw-user-thumbnail lw-lazy-img" />
		</a>
		<div class="card-title">
			<h5>
				<a class="text-secondary" href="<?= route('user.profile_view', ['username' => $filter['username']]) ?>">
					<?= $filter['fullName'] ?>
				</a>
				<?= $filter['detailString'] ?> <br>
				@if($filter['countryName'])
				<?= $filter['countryName'] ?>
				@endif
			</h5>
		</div>
	</div>
</div>
@endforeach
@else
<!-- info message -->
<div class="col-sm-12 alert alert-info">
	<?= __tr('There are no matches found.') ?>
</div>
<!-- / info message -->
@endif