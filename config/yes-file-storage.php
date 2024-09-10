<?php
/*
 *  YesFileStorage Configurations
 *
 *  This configuration file is part of YesFileStorage
 *
 *------------------------------------------------------------------------------------------------*/

return [

    /* Restrictions for elements to be uploaded
     *--------------------------------------------------------------------------------------------*/
    'element_config' => [
        'all' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG'],
            ],
        ],
        'logo' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/png', 'image/PNG', 'image/svg', 'image/svg+xml'],
            ],
        ],
        'small_logo' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/png', 'image/PNG', 'image/svg', 'image/svg+xml'],
            ],
        ],
        'favicon' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/ico', 'image/vnd.microsoft.icon'],
            ],
        ],
        'profile' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG', 'image/png', 'image/gif'],
            ],
        ],
        'cover_image' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG', 'image/png'],
            ],
        ],
        'photos' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG', 'image/png', 'image/gif'],
            ],
        ],
        'gift' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG', 'image/png', 'image/gif', 'image/svg', 'image/svg+xml'],
            ],
        ],
        'sticker' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG', 'image/png', 'image/gif', 'image/svg', 'image/svg+xml'],
            ],
        ],
        'package' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG'],
            ],
        ],
        'messenger' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/jpg', 'image/JPG', 'image/jpeg', 'image/JPEG', 'image/png'],
            ],
        ],
        'language' => [
            'restrictions' => [
                'allowedFileTypes' => ['image/xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ],
        ],
    ],

    /* Uploaded media path
     *--------------------------------------------------------------------------------------------*/
    'storage_paths' => [
        'temp' => 'key@language_file', // it will be in local storage
        env('STORAGE_BASE_FOLDER', '').'media-storage' => [
            'logo' => 'key@logo',
            'small_logo' => 'key@small_logo',
            'favicon' => 'key@favicon',
            'users' => [
                '{_uid}' => [
                    '' => 'key@user',
                    'temp_uploads' => 'key@user_temp_uploads',
                    'profile' => 'key@profile_photo',
                    'cover' => 'key@cover_photo',
                    'photos' => 'key@user_photos',
                ],
            ],
            'gift' => [
                '{_uid}' => [
                    '' => 'key@gift_image',
                ],
            ],
            'sticker' => [
                '{_uid}' => [
                    '' => 'key@sticker_image',
                ],
            ],
            'package' => [
                '{_uid}' => [
                    '' => 'key@package_image',
                ],
            ],
            'messenger' => [
                '{_uid}' => [
                    '' => 'key@messenger_file',
                ],
            ],
        ],
    ],
];
