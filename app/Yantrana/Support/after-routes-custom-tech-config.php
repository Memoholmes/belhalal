<?php

// set configuration items
/* config([
    'services.facebook.redirect' => route('social.user.login.callback', [getSocialProviderKey('facebook')]),
    'services.google.redirect' => route('social.user.login.callback', [getSocialProviderKey('google')]),
]); */
// set configuration items
config([
    'services.facebook.client_id' => getStoreSettings('facebook_client_id'),
    'services.facebook.client_secret' => getStoreSettings('facebook_client_secret'),
    'services.facebook.redirect'      	=> route('social.user.login.callback', [getSocialProviderKey('facebook')]), // done in after-rotes-custom-tech-config.php
    'services.google.client_id' => getStoreSettings('google_client_id'),
    'services.google.client_secret' => getStoreSettings('google_client_secret'),
    'services.google.redirect'      	=> route('social.user.login.callback', [getSocialProviderKey('google')]), // done in after-rotes-custom-tech-config.php
    'filesystems.public-media-storage.url' => url(''),
]);

if (getStoreSettings('use_env_default_email_settings') == false) {
    config([
        // Mail driver
        'mail.driver' => getStoreSettings('mail_driver'),
        'mail.transport' => getStoreSettings('mail_driver'),

        // Mail Setting for SMTP and Mandrill
        'mail.port' => getStoreSettings('smtp_mail_port'),
        'mail.host' => getStoreSettings('smtp_mail_host'),
        'mail.username' => getStoreSettings('smtp_mail_username'),
        'mail.encryption' => getStoreSettings('smtp_mail_encryption'),
        'mail.password' => getStoreSettings('smtp_mail_password_or_apikey'),
        'mail.from.address' => getStoreSettings('mail_from_address'),
        'mail.from.name' => getStoreSettings('mail_from_name'),
        // Mail Setting for Sparkpost
        'services.sparkpost.secret' => getStoreSettings('sparkpost_mail_password_or_apikey'),
        // Mail Setting for Mailgun
        'services.mailgun.domain' => getStoreSettings('mailgun_domain'),
        'services.mailgun.secret' => getStoreSettings('mailgun_mail_password_or_apikey'),
        'services.mailgun.endpoint' => getStoreSettings('mailgun_endpoint'),
        '__tech.mail_from' => [
            getStoreSettings('mail_from_address'), getStoreSettings('mail_from_name'),
        ],
    ]);
}
