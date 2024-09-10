@section('page-title', __tr('Blocked Users'))
@section('head-title', __tr('Blocked Users'))
@section('keywordName', __tr('Blocked Users'))
@section('keyword', __tr('Blocked Users'))
@section('description', __tr('Blocked Users'))
@section('keywordDescription', __tr('Blocked Users'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h5 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fas fa-ban"></i></span> <?= __tr('Blocked Users') ?>
	</h5>
</div>

<!-- blocked users -->
<div class="container-fluid">
	@if(!__isEmpty($usersData))
	<div class="row row-cols-sm-1 row-cols-md-3 row-cols-lg-6 row-cols-xl-8" id="lwLoadMoreContentContainer">
		@include('user.partial-templates.blocked-users')
	</div>
	@else
	<!-- info message -->
	<div class="alert alert-info">
		<?= __tr('There are no blocked users.') ?>
	</div>
	<!-- / info message -->
	@endif
</div>
<!-- / blocked users -->
@lwPush('appScripts')
<script>
	//get block user data
	var blockUserData = JSON.parse('<?= json_encode($usersData) ?>');

	//if block user length is zero then show info message
	if (blockUserData.length == 0) {
		$("#lwShowInfoMessage").show();
	} else {
		$("#lwShowInfoMessage").hide();
	}

	//on un block user callback
	function onUnblockUser(response) {
		//check reaction code is 1
		if (response.reaction == 1) {
			var requestData = response.data;

			//apply class row fade in
			$("#lwBlockUser_" + requestData.blockUserUid).parent().fadeOut();
			if (requestData.blockUserLength == 0) {
				$("#lwShowInfoMessage").show();
			}
		}
	}
</script>
@lwPushEnd