<div class="lw-messenger">
    <div class="row">
        <div class="lw-messenger-sidebar col-md-4 p-0 pl-3 pr-1 ">
            <div class="lw-messenger-header shadow">
                <img src="<?= $currentUserData['logged_in_user_profile_picture'] ?>"
                    class="lw-profile-picture lw-online" alt="">
                <div class="align-self-center lw-profile-name">
                    <?= $currentUserData['logged_in_user_full_name'] ?>
                    <div class="ml-2 d-inline">
                        <a href data-toggle="modal" data-user-id="<?= $currentUserData['logged_in_user_id'] ?>" data-target="#adminLoginDialog" data-user-name="<?= $currentUserData['logged_in_user_full_name'] ?>"><i class="fas fa-sign-in-alt text-secondary" title="Login as"></i></a>
                    </div>
                    <div class="w-100 text-muted">
                        <small>
                            <?= Str::limit($currentUserData['logged_in_user_about_me'], 15) ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="lw-messenger-contact-list">
                <div class="lw-messenger-contact-search">
                    <input type="text" id="lwFilterUsers" class="form-control text-dark" placeholder="Type to filter">
                </div>
                <div class="list-group list-group-flush">
                    <!-- Check if messenger users exists -->
                    @if(!__isEmpty($messengerUsers))
                    @foreach($messengerUsers as $messengerUser)
                    <a href="#" class="list-group-item list-group-item-action lw-ajax-link-action lw-user-chat-list"
                        data-action="<?= route('user.read.fake_user_conversation', ['userId' => $messengerUser['user_id'],'fake_user_id'=> $messengerUser['fake_user_id']]) ?>"
                        id="<?= $messengerUser['user_id'] ?>" data-callback="userChatResponse" data-fakeUserId="<?= $messengerUser['fake_user_id'] ?>">
                        @if($messengerUser['is_online'] == 1)
                        <span class="lw-contact-status lw-online"></span>
                        @elseif($messengerUser['is_online'] == 2)
                        <span class="lw-contact-status lw-away"></span>
                        @elseif($messengerUser['is_online'] == 3)
                        <span class="lw-contact-status lw-offline"></span>
                        @endif

                        <img src="<?= $messengerUser['profile_picture'] ?>" class="lw-profile-picture lw-online" alt="">
                        <?= $messengerUser['user_full_name'] ?>
                        <span
                            class="badge badge-pill badge-success lw-incoming-message-count-<?= $messengerUser['user_id'] ?>"></span>
                    </a>
                    @endforeach
                    @endif
                    <!-- /Check if messenger users exists -->
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="adminLoginDialog" tabindex="-1" role="dialog" aria-labelledby="adminLoginDialogLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div id="lwAdminLoginContent"></div>
                        <script type="text/_template" id="lwAdminLoginTemplate" data-replace-target="#lwAdminLoginContent" data-modal-id="#adminLoginDialog">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="lwadminLoginModalLabel"><?= __tr('Login as') ?> <%= __tData.userName %></h5>
                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center">
                                        <p>You are login as <%= __tData.userName %></p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal"><?= __tr('Cancel') ?></button>
                                    <a target="_blank" class="btn btn-primary btn-sm"  href="<%= __Utils.apiURL("<?= route('admin.login.fake.user_profile', ['userId' => 'userId']) ?>", {'userId': __tData.loginContent.userId}) %>" data-method="post"><i class="fas fa-sign-in-alt fa-fw"></i> <?= __tr('Yes') ?></a>
                                </div>
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="lw-messenger-content col-md-8" id="lwUserConversationContainer"></div>
    </div>

    <script>
        __Messenger.sendMessageRawUrl = "<?= route('fake_user.write.send_message', ['userId' => 'userId']) ?>";
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

        __Utils.modalTemplatize('#lwAdminLoginTemplate', function(e, data) {

            return {
                'loginContent': loginContent(data), //fetch user data
                'userName': data['userName']
            };
        }, function(e, myData) {});

        function loginContent(data) {
            return data;
        }
    </script>