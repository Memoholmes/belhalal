<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <li>
        <a class="sidebar-brand d-flex align-items-center bg-dark" href="<?= url('/home') ?>">
            <div class="sidebar-brand-icon">
                <img class="lw-logo-img" src="<?= getStoreSettings('small_logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
            </div>
            <img class="lw-logo-img d-sm-none d-none d-md-block" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
            <img class="lw-logo-img d-sm-block d-md-none" src="<?= getStoreSettings('small_logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
        </a>
    </li>
    <li class="nav-item mt-2 d-sm-block d-md-none">
        <a href class="nav-link" onclick="getChatMessenger('<?= route('user.read.all_conversation') ?>', true)" id="lwAllMessageChatButton" data-chat-loaded="false" data-toggle="modal" data-target="#messengerDialog">
            <span class="badge badge-danger badge-counter lw-new-message-badge"></span>
            <i class="far fa-comments"></i>
            <span><?= __tr('Messenger') ?></span>
        </a>
    </li>
    <!-- Nav Item - Messages -->
    <li class="nav-item d-sm-block d-md-none">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.credit_wallet.read.view') ?>">
            <i class="fas fa-coins fa-fw mr-2"></i>
            <span><?= __tr('Credit Wallet') ?></span>
            <span class="badge badge-success badge-counter"><?= totalUserCredits() ?></span>
        </a>
    </li>

    <!-- Nav Item - Messages -->
    <li class="nav-item d-sm-block d-md-none">
        <a class="nav-link" href data-toggle="modal" onclick="showBoosterAlert()">
            <i class="fas fa-bolt fa-fw mr-2"></i>
            <span><?= __tr('Profile Booster') ?></span>
            <span id="lwBoosterTimerCountDownOnSB"></span>
        </a>
    </li>
  {{--   @if(isset($is_profile_page) and ($is_profile_page === true))
    @if(!$isBlockUser and !$blockByMeUser)
    @stack('sidebarProfilePage')
    @endif
    @endif --}}
    <hr class="sidebar-divider mt-2 mb-2 d-sm-block d-md-none">
    <!-- Heading -->
    <li class="mt-4 nav-item <?= makeLinkActive('home_page') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('home_page') ?>">
            <i class="fas fa-home"></i>
            <span><?= __tr('Home') ?></span>
        </a>
    </li>

    <li class="nav-item <?= makeLinkActive('user.read.find_matches') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.read.find_matches') ?>">
            <i class="fas fa-search"></i>
            <span><?= __tr('Find Matches') ?></span>
        </a>
    </li>
    <li class="nav-item <?= makeLinkActive('user.profile_view') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" href="<?= route('user.profile_view', ['username' => getUserAuthInfo('profile.username')]) ?>">
            <i class="fas fa-user"></i>
            <span><?= __tr('My Profile') ?></span>
        </a>
    </li>
    <li class="nav-item <?= makeLinkActive('user.photos_setting') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" data-event-callback="lwPrepareUploadPlugIn" href="<?= route('user.photos_setting', ['username' => getUserAuthInfo('profile.username')]) ?>">
            <i class="far fa-images"></i>
            <span><?= __tr('My Photos') ?></span>
        </a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider mt-2 mb-2">
    <li class="nav-item <?= makeLinkActive('user.who_liked_me_view') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.who_liked_me_view') ?>">
            <i class="fa fa-thumbs-up text-warning" aria-hidden="true"></i>
            <span><?= __tr('Who likes me') ?>
                <?php
                $featurePlans = getStoreSettings('feature_plans');
                $showLike = $featurePlans['show_like']['select_user'];
                ?>
                @if($showLike == 2)
                <span class="lw-premium-feature-badge lw-premium-feature-width" title="{{ __tr('This is Premium feature') }}"></span></span>
            @endif
        </a>
    </li>
    <li class="nav-item <?= makeLinkActive('user.mutual_like_view') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.mutual_like_view') ?>">
            <i class="fa fa-users text-danger"></i>
            <span><?= __tr('Mutual Likes') ?></span>
        </a>
    </li>
    <li class="nav-item <?= makeLinkActive('user.my_liked_view') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.my_liked_view') ?>">
            <i class="fas fa-fw fa-heart text-success"></i>
            <span><?= __tr('My Likes') ?></span>
        </a>
    </li>
    <li class="nav-item <?= makeLinkActive('user.my_disliked_view') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.my_disliked_view') ?>">
            <i class="fas fa-fw fa-heart-broken text-info"></i>
            <span><?= __tr('My Dislikes') ?></span>
        </a>
    </li>
    <li class="nav-item  <?= makeLinkActive('user.profile_visitors_view') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.profile_visitors_view') ?>">
            <i class="fa fa-user" aria-hidden="true"></i>
            <span><?= __tr('Visitors') ?></span>
        </a>
    </li>
    <li x-data="{totalNotificationCount:'<?= (getNotificationList()['notificationCount'] > 0) ? getNotificationList()['notificationCount'] : '' ?>'}" class="nav-item  <?= makeLinkActive('user.notification.read.view') ?>">
        <a class="nav-link  lw-ajax-link-action lw-action-with-url" href="<?= route('user.notification.read.view') ?>">
            <i class="fa fa-bell" aria-hidden="true"></i>
            <span><?= __tr('Notifications') ?> <small class="badge badge-danger" x-text="totalNotificationCount"></small></span>
        </a>
    </li>
    <li class="nav-item <?= makeLinkActive('user.read.block_user_list') ?>">
        <a class="nav-link lw-ajax-link-action lw-action-with-url" href="<?= route('user.read.block_user_list') ?>">
            <i class="fas fa-ban"></i>
            <span><?= __tr('Blocked Users') ?></span>
        </a>
    </li>
    <div class="card mt-3 lw-featured-users-block">
        <h5 class="card-header">
            <?= __tr('Featured Users') ?>
        </h5>
        <div class="card-body lw-featured-users">
            <button type="button" class="btn btn-icon" id="lw-default-featured-users" title="{{ __tr('Get yourself in Featured Users') }}"> <i class="fa fa-2x fa-user-plus"></i></button>
            @if(!__isEmpty(getFeatureUserList()))
            @foreach(getFeatureUserList() as $users)
            <a class="lw-ajax-link-action lw-action-with-url" href="<?= route('user.profile_view', ['username' => $users['username']]) ?>">
                <img class="img-fluid img-thumbnail lw-sidebar-thumbnail lw-lazy-img" data-src="<?= $users['userImageUrl'] ?>">
            </a>
            @endforeach
            @endif
        </div>
    </div>

    <!-- sidebar advertisement -->
    @if(!getFeatureSettings('no_adds') and getStoreSettings('user_sidebar_advertisement')['status'] == 'true')
    <li class="nav-item lw-sidebar-ads-container d-none d-md-block">
        <!-- sidebar advertisement content -->
        <div>
            <?= getStoreSettings('user_sidebar_advertisement')['content'] ?>
        </div>
        <!-- /sidebar advertisement content -->
    </li>
    <!-- sidebar advertisement -->
    @endif
    <!-- Sidebar Toggler (Sidebar) -->
</ul>
<!-- End of Sidebar -->