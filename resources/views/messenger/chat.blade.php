<div class="lw-messenger">
    <div class="row">
        <div class="lw-messenger-sidebar col-md-4 p-0 pl-3 pr-1 ">
            <div class="lw-messenger-header shadow">
                <img src="<?= $currentUserData['logged_in_user_profile_picture'] ?>" class="lw-profile-picture lw-online" alt="">
                <div class="align-self-center lw-profile-name">
                    <?= $currentUserData['logged_in_user_full_name'] ?>
                    <div class="w-100 text-muted">
                        <small>
                            <?= Str::limit($currentUserData['logged_in_user_about_me'], 15) ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="lw-messenger-contact-list">
                <div class="lw-messenger-contact-search">
                    <input type="text" id="lwFilterUsers" class="form-control text-dark" placeholder="<?= __tr("Type to filter") ?>">
                </div>
                <div class="list-group list-group-flush">
                    <!-- Check if messenger users exists -->
                    @if(!__isEmpty($messengerUsers))
                    @foreach($messengerUsers as $messengerUser)
                    <a href="#" class="list-group-item list-group-item-action lw-ajax-link-action lw-user-chat-list" data-action="<?= route('user.read.user_conversation', ['userId' => $messengerUser['user_id']]) ?>" id="<?= $messengerUser['user_id'] ?>" data-callback="userChatResponse">
                        @if($messengerUser['is_online'] == 1)
                        <span class="lw-contact-status lw-online"></span>
                        @elseif($messengerUser['is_online'] == 2)
                        <span class="lw-contact-status lw-away"></span>
                        @elseif($messengerUser['is_online'] == 3)
                        <span class="lw-contact-status lw-offline"></span>
                        @endif

                        <img src="<?= $messengerUser['profile_picture'] ?>" class="lw-profile-picture lw-online" alt="">
                        <?= $messengerUser['user_full_name'] ?>
                        <span class="badge badge-pill badge-success lw-incoming-message-count-<?= $messengerUser['user_id'] ?>"></span>
                    </a>
                    @endforeach
                    @endif
                    <!-- /Check if messenger users exists -->
                </div>
            </div>
        </div>
        <div class="lw-messenger-content col-md-8" id="lwUserConversationContainer"></div>
    </div>
    <script>
        __Messenger.sendMessageRawUrl = "<?= route('user.write.send_message', ['userId' => 'userId']) ?>";
        __Messenger.buyStickerUrl = "<?= route('user.write.buy_stickers') ?>";
        __Messenger.giphyKey = "<?= getStoreSettings('giphy_key') ?>";
        __Messenger.loggedInUserProfilePicture = "<?= $currentUserData['logged_in_user_profile_picture'] ?>";
        __Messenger.loggedInUserUid = "<?= getUserUID() ?>";
        __Messenger.pusherAppKey = "<?= getStoreSettings('pusher_app_key') ?>";

        // Select a list of user chat 
        var $userListGroup = $('.lw-user-chat-list');
        // Fire click event on first element
        $($userListGroup[0]).trigger("click");
        // Add Active class to first element
        $($userListGroup[0]).addClass('active');
        // Click event fire when click on user list
        $userListGroup.click(function(e) {
            if ($(this).hasClass('active')) {
                e.stopPropagation();
            }
            $('.lw-messenger-contact-list a.active').removeClass('active');
            $(this).addClass('active');
            __Messenger.toggleSidebarOnMobileView();
            var incomingMsgEl = $('.lw-incoming-message-count-' + $(this).attr('id'));
            if (!_.isEmpty(incomingMsgEl.text())) {
                incomingMsgEl.text(null);
            }
        });
        // lwFilterUsers
        $("#lwFilterUsers").on("keyup", function() {
            var filterQuery = $(this).val().toLowerCase();
            $(".lw-messenger-contact-list a").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(filterQuery) > -1)
            });
        });
    </script>