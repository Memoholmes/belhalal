<?php
// added to prevent bad gateway in some cases - 13 AUG 2023
if(function_exists('putenv')) {
    putenv('LC_ALL=en_US');
}
// PHP GZip compression
/* if (function_exists('ob_gzhandler') and (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))) {
    ob_start("ob_gzhandler");
} */

// default locale
$locale = 'en_US';
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (! function_exists('changeAppLocale')) {
    function changeAppLocale($localeId = null, $localeConfig = null)
    {
        // define constants for locale
        if (! defined('LOCALE_DIR')) {
            define('LOCALE_DIR', base_path('locale'));
        }
        if (! $localeConfig) {
            $localeConfig = config('locale');
        }
        $availableLocale = getStoreSettings('translation_languages');

        if (__isEmpty($availableLocale)) {
            $availableLocale = [];
        }
        $availableLocale['en_US'] = [
            'id' => 'en_US',
            'name' => __tr('English'),
            'is_rtl' => false,
            'status' => true,
        ];
        $userBrowserLocale = $locale = getStoreSettings('default_language');
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) and function_exists('locale_accept_from_http')) {
            $userBrowserLocale = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
        // check if language is exist
        if ($userBrowserLocale and array_key_exists($userBrowserLocale, $availableLocale)) {
            $locale = $userBrowserLocale;
        } else {
            $userBrowserLocale = substr($userBrowserLocale, 0, 2);
            if ($userBrowserLocale and array_key_exists($userBrowserLocale, $availableLocale)) {
                $locale = $userBrowserLocale;
            }
        }
        if ($selectedLocale = getUserSettings('selected_locale')) {
            $locale = $selectedLocale;
        }
        // check if locale is available
        if ($localeId and array_key_exists($localeId, $availableLocale)) {
            $locale = $localeId;
            // set current locale in session
            $_SESSION['CURRENT_LOCALE'] = $locale;
            // check if current locale is already set if yes use it
        } elseif (isset($_SESSION['CURRENT_LOCALE']) and $_SESSION['CURRENT_LOCALE']) {
            $locale = $_SESSION['CURRENT_LOCALE'];
        }
        // define constant for current locale
        if (! config('CURRENT_LOCALE_DIRECTION')) {
            $direction = 'ltr';
            if (
                isset($availableLocale[$locale])
                and $availableLocale[$locale]['is_rtl'] == true
            ) {
                $direction = 'rtl';
            }
            // define('CURRENT_LOCALE_DIRECTION', $direction);
            config([
                'CURRENT_LOCALE_DIRECTION' => $direction,
            ]);
        }
        // define constant for current locale
        if (! config('CURRENT_LOCALE')) {
            // define('CURRENT_LOCALE', $locale);
            config([
                'CURRENT_LOCALE' => $locale,
            ]);
        }

        $domain = 'messages';
        if(function_exists('putenv')) {
            putenv('LC_ALL='.$locale.'.utf8');
        }

        T_setlocale(LC_ALL, $locale.'.utf8');
        T_bindtextdomain($domain, LOCALE_DIR);
        T_bind_textdomain_codeset($domain, 'UTF-8');
        T_textdomain($domain);

        \App::setLocale(substr($locale, 0, 2));
        \Carbon\Carbon::setLocale($locale, 'UTF-8');
    }
}
