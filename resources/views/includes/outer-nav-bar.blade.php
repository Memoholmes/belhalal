<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container-fluid">
        <a class="navbar-brand js-scroll-trigger" href="{{ url('/') }}#page-top">
            <img class="lw-logo-img" src="<?= getStoreSettings('logo_image_url') ?>"
                alt="<?= getStoreSettings('name') ?>">
        </a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
            data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
            aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars"></i>
        </button>
        <?php $translationLanguages = getActiveTranslationLanguages(); ?>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="{{ url('/') }}#premium"><?= __tr('Premium') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="{{ url('/') }}#features"><?= __tr('Features') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="<?= route('user.read.contact') ?>"><?= __tr('Contact') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= route('user.login') ?>"><?= __tr('Login') ?></a>
                </li>
                <!-- Language Menu -->
                @if (!__isEmpty($translationLanguages) and count($translationLanguages) > 1)
                    <?php $translationLanguages['en_US'] = configItem('default_translation_language'); ?>
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span
                                class="d-none d-md-inline-block"><?= isset($translationLanguages[config('CURRENT_LOCALE')]) ? $translationLanguages[config('CURRENT_LOCALE')]['name'] : '' ?></span>
                            &nbsp; <i class="fas fa-language"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <h6 class="dropdown-header">
                                <?= __tr('Choose your language') ?>
                            </h6>
                            <div class="dropdown-divider"></div>
                            <?php foreach ($translationLanguages as $languageId => $language) {
            if ($languageId == config('CURRENT_LOCALE') or (isset($language['status']) and $language['status'] == false)) continue;
          ?>
                            <a class="dropdown-item"
                                href="<?= route('locale.change', ['localeID' => $languageId]) . '?redirectTo=' . base64_encode(Request::fullUrl()) ?>">
                                <?= $language['name'] ?>
                            </a>
                            <?php } ?>
                        </div>
                    </li>
                @endif
                <!-- Language Menu -->
            </ul>
        </div>
    </div>
</nav>