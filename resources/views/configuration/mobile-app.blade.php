@section('page-title', __tr("Mobile App Configurations"))
@section('head-title', __tr("Mobile App Configurations"))
@section('keywordName', strip_tags(__tr("Mobile App Configurations")))
@section('keyword', strip_tags(__tr("Mobile App Configurations")))
@section('description', strip_tags(__tr("Mobile App Configurations")))
@section('keywordDescription', strip_tags(__tr("Mobile App Configurations")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

@lwPush('header')
<style>
.error {
color: #ff0000;
font-size: initial;
position: relative;
width: inherit;
}
</style>
@lwPushEnd
@php
    $isDemoMode = (isDemo() and (isAdmin() and (getUserID() != 1)));
    $demoContent = 'XXXXXXXXXXXXX MASKED FOR DEMO XXXXXXXXXXXXX';
@endphp
<!-- Page Heading -->
<div class="col-12">
<div class="d-sm-flex align-items-center justify-content-between mb-4">
<h1 class="h3 mb-0 text-gray-200"><?= __tr('Mobile App Configurations') ?></h1>
</div>
<div class="col-12 mb-3 alert alert-success">
<?= __tr('If you have purchase Flutter Mobile App or Bundle of for this application. You need following configurations contents for app_config.dart file for Flutter Mobile apps.') ?>
</div>
<textarea style="height: 80vh" class="form-control" readonly name="mobile_app_config" id="mobileAppConfig" rows="50">
// This is the mobile app configuration file content you can make
// changes to the file as per your requirements
// do not change start -------------------------------------------

const String baseUrl = '{{ url('/') }}/';
const String baseApiUrl = '${baseUrl}api/';

// key for form encryption/decryptions
const String publicKey = '''{!! $isDemoMode ? $demoContent : YesSecurity::getPublicRsaKey() !!}''';

// ------------------------------------------- do not change end

// if you want to enable debug mode set it to true
// for the production make it false
const bool debug = {{ config('app.debug') ? 'true' : 'false' }};
const String version = '1.8.0';
const Map configItems = {
    'debug': debug,
    'appTitle': '{{ getStoreSettings('name') }}',
    // ads will work based on No ads feature settings
    'ads': {
        'enable': false,
        // banner ad on other user's profile page
        'profile_banner_ad': {
            'enable': false,
          // sample test ads
          'android_ad_unit_id': 'ca-app-pub-3940256099942544/6300978111',
          'ios_ad_unit_id': 'ca-app-pub-3940256099942544/2934735716',
          // live
          // 'android_ad_unit_id': '',
          // 'ios_ad_unit_id': '',
        },
        // fullscreen ads that will display to user at certain frequency
        'interstitial_id': {
            'enable': false,
          // sample test ads
           'android_ad_unit_id': 'ca-app-pub-3940256099942544/1033173712',
          'ios_ad_unit_id': 'ca-app-pub-3940256099942544/4411468910',
          // live
          // 'android_ad_unit_id': '',
          // 'ios_ad_unit_id': '',
          'frequency_in_seconds': 300,
        }
      },
    'creditPackages': {
        // as of now in app purchase for iOS is not available and will be available soon.
        'enablePurchase': true,
        'productIds': [
        // credit package uids, you should use it for product ids in Google In App
        @if ($isDemoMode)
'{{ $demoContent }}'
        @else
        @foreach ($creditPackages as $creditPackage)
// Package Title - {{ $creditPackage->title }}
        '{{ toggleProductId($creditPackage->_uid) }}',
        @endforeach
        @endif
        ],
    },
    'services': {
        'agora': {
            'appId': '{{ $isDemoMode ? $demoContent : getStoreSettings('agora_app_id') }}',
        },
        'pusher': {
            'apiKey': '{{ $isDemoMode ? $demoContent : getStoreSettings('pusher_app_key') }}',
            'cluster': '{{ $isDemoMode ? $demoContent : getStoreSettings('pusher_app_cluster_key') }}',
        },
        'giphy': {
            'enable': {{ ($isDemoMode ? $demoContent : (getStoreSettings('allow_giphy') ? 'true' : 'false')) }},
            'apiKey': '{{ $isDemoMode ? $demoContent : getStoreSettings('giphy_key') }}',
            'features': {
                'showEmojis': true,
                'showStickers': true,
                'showGIFs': true,
            }
        }
    },
    'social_logins': {
        'google': {
            // if enabled you need to configure as suggested in help guide
            'enable': false,
            // mostly directly useful for iOS
            'client_id':''
          },
        'facebook': {
          // if enabled you need to configure it for android and ios as suggested in help guide
          'enable': false,
        }
    }
};
</textarea>
</div>