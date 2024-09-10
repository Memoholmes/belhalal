<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-dark topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <strong>
        <?= __tr('Admin Section') ?>
    </strong>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        {{-- <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li> --}}
        <?php $translationLanguages = getActiveTranslationLanguages(); ?>
        <!-- Language Menu -->
        @if(!__isEmpty($translationLanguages) and (count($translationLanguages) > 1))
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= (isset($translationLanguages[config('CURRENT_LOCALE')])) ? $translationLanguages[config('CURRENT_LOCALE')]['name'] : '' ?> &nbsp; <i class="fas fa-language"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <?php foreach ($translationLanguages as $languageId => $language) {
                    
                    if (($languageId == config('CURRENT_LOCALE')) or (isset($language['status']) and $language['status'] == false)) continue;
                ?>
                    <a class="dropdown-item lw-ajax-link-action" data-callback="__Utils.viewReload" href="<?= route('locale.change', ['localeID' => $languageId]);  ?>">
                        <?= $language['name'] ?>
                    </a>
                <?php } ?>
            </div>
        </li>
        @endif
        <!-- Language Menu -->

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 ml-2 d-none d-lg-inline text-gray-600 small"><?= getUserAuthInfo('profile.full_name') ?></span>
                <img class="img-profile rounded-circle" src="<?= getUserAuthInfo('profile.profile_picture_url') ?>">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?= route('user.profile_view', ['username' => getUserAuthInfo('profile.username')]) ?>">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?= __tr('Profile') ?>
                </a>
                <a class="dropdown-item" href="<?= route('user.change_password') ?>">
                    <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?= __tr('Change Password') ?>
                </a>
                <a class="dropdown-item" href="<?= route('user.change_email') ?>">
                    <i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?= __tr('Change Email') ?>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?= __tr('Logout') ?>
                </a>
            </div>
        </li>
    </ul>
</nav>
<!-- End of Topbar -->

<!-- for image gallery -->
@include('includes.image-gallery')