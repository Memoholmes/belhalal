@section('page-title', __tr('Notifications'))
@section('head-title', __tr('Notifications'))
@section('keywordName', __tr('Notifications'))
@section('keyword', __tr('Notifications'))
@section('description', __tr('Notifications'))
@section('keywordDescription', __tr('Notifications'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h5 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fas fa-bell"></i></span> <?= __tr('Notifications') ?></h5>
</div>

<!-- Start of Notification Wrapper -->
<div class="card mb-4">
	<div class="card-body">
		<x-lw.datatable id="lwManageUserPhotosTable" :url="route('user.notification.read.list')">
			<th data-template="#notificationMsgActionTemplate" data-orderable="true" data-name="message"><?= __tr('Notification For') ?></th>
				<th data-order-by="true" data-order-type="desc" data-template="#notificationTimeActionTemplate" data-orderable="true"  data-name="created_at"><?= __tr('Time') ?></th>
		</x-lw.datatable>
	</div>
</div>
<!-- End of Notification Wrapper -->

<!-- Notification Msg Action Column -->
<script type="text/_template" id="notificationMsgActionTemplate">
	<!-- Notification Msg link -->
	<%= __tData.message %> <a href="<%= __tData.action %>"><i class="fas fa-user-circle"></i></a>
	<!-- /Notification Msg link -->
</script>
<!-- Notification Msg Action Column -->

<!-- Notification Msg Action Column -->
<script type="text/_template" id="notificationTimeActionTemplate">
	<!-- Notification Time link -->
	<span title="<%= __tData.created_at %>"><%= __tData.formattedCreatedAt %></span>
	<!-- /Notification Time link -->
</script>
<!-- Notification Msg Action Column -->

@lwPush('appScripts')
<script>
	//notification read callback
	function notificationReadCallback(response) {
		if (response.reaction == 1) {
			//reload data-table instance
			reloadDT(notificationTableInstance);
			//get notification list
			var requestData = response.data.getNotificationList,
				getNotificationList = requestData.notificationData,
				getNotificationCount = requestData.notificationCount,
				notification = '';
			//empty text
			$("#lwNotificationList").text('');
			if (!_.isEmpty(getNotificationList)) {
				_.forEach(getNotificationList, function(value, key) {
					notification = '<a class="dropdown-item d-flex align-items-center"><div><div class="small text-gray-500">' + value.created_at + '</div><span class="font-weight-bold">' + value.message + '</span></div></a>';
					$("#lwNotificationList").append(notification);
				});
			} else {
				//hide show all notification link in top header
				$("#lwShowAllNotifyLink").hide();
				notification = '<a class="dropdown-item text-center small text-gray-500"><?= __tr('There are no notification.') ?></a>'
			}
			$("#lwNotificationCount").text(getNotificationCount);
		}
	}
</script>
@lwPushEnd