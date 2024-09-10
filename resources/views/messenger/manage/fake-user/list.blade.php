<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200">
		<?= __tr('Fake User Messenger') ?>
	</h1>
</div>
<div class="row">
    <div class="lw-messenger-sidebar col-md-2 p-0 ">
        <div class="lw-fake-user-messenger-list">
            <div class="list-group list-group-flush">
                <!-- Check if messenger users exists -->
                @if(!__isEmpty($fakeUsersProfiles))
                @foreach($fakeUsersProfiles as $fakeUserProfile)
                <a href="#" class="list-group-item list-group-item-action lw-ajax-link-action lw-fake-user-chat-list"
                    data-action="<?= route('manage.fake_users.list',['fakeUserId' => $fakeUserProfile['user_id']]) ?>"
                    id="<?= $fakeUserProfile['user_id'] ?>">
                    {{ $fakeUserProfile['full_name'] }}
                    <span
                            class="badge badge-pill badge-success lw-fake-user-incoming-message-count-<?= $fakeUserProfile['user_id'] ?>"></span>
                </a>
                @endforeach
                @endif
                <!-- /Check if messenger users exists -->
            </div>
        </div>
    </div>
    <div class="lw-messenger-content col-md-10 p-0" id="lwFakeUserConversationContainer"></div>
</div>
@lwPush('appScripts')
<script>
    // Select a list of user chat
    var $fakeUserListGroup = $('.lw-fake-user-chat-list');

    $($fakeUserListGroup[0]).trigger("click");
        // Add Active class to first element
        $($fakeUserListGroup[0]).addClass('active');
        // Click event fire when click on user list
        $fakeUserListGroup.click(function(e) {
            if ($(this).hasClass('active')) {
                e.stopPropagation();
            }
            $('.lw-fake-user-messenger-list a.active').removeClass('active');
            $(this).addClass('active');
            __Messenger.toggleSidebarOnMobileView();
            var incomingMsgEl = $('.lw-fake-user-incoming-message-count-' + $(this).attr('id'));
            if (!_.isEmpty(incomingMsgEl.text())) {
                incomingMsgEl.text(null);
            }
        });
</script>
@lwPushEnd