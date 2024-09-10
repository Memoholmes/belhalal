(function ($) {
    "use strict"; // Start of use strict

    __globals.translate_strings['uploader_default_text'] = "<span class='filepond--label-action'>Drag & Drop Files or Browse</span>";

    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click tap', function (e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function () {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    if ($(window).width() < 768) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
    };

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function (e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function () {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function (e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });

})(jQuery); // End of use strict

// Use for filepond file uploader
var fileUploaderInit = function () {
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFilePoster,
        FilePondPluginFileValidateType
    );
    $('.lw-file-uploader').each(function (index, uploader) {
        var actionUrl = $(this).data('action'),
            responseCallback = $(this).data('callback'),
            defaultImage = $(this).data('default-image-url'),
            removeMediaAfterUpload = $(this).data('remove-media'),
            allowReplace = $(this).data('allow-replace'),
            allowRevert = $(this).data('allow-revert'),
            removeAllMediaAfterUpload = $(this).data('remove-all-media'),
            allowedMediaExtension = $(this).data('allowed-media'),
            filePondAdditionalOptions = {
                maxParallelUploads: 10,
                imagePreviewMaxHeight: 175,
                labelIdle: $(this).data('label-idle') ? $(this).data('label-idle') : __Utils.getTranslation('uploader_default_text'),
                acceptedFileTypes: allowedMediaExtension,
                fileValidateTypeDetectType: function (source, type) {
                    return new Promise(function (resolve, reject) {
                        if (allowedMediaExtension) {
                            if (allowedMediaExtension.indexOf(type) < 0) {
                                reject();
                            }
                        }
                        resolve(type);
                    })
                },
                allowRevert: allowRevert ? allowRevert : false,
                allowReplace: allowReplace ? allowReplace : false,
                credits:false,
                server: {
                    process: {
                        url: actionUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': appConfig.csrf_token
                        },
                        withCredentials: false,
                        onload: function (response) {
                            var requestData = JSON.parse(response);
                            // Show message when upload complete
                            switch (requestData.reaction) {
                                case 1:
                                    $('.lw-uploaded-file').val(requestData.data.fileName);
                                    showSuccessMessage(requestData.data.message);
                                    break;
                                case 14:
                                    showWarnMessage(requestData.data.message);
                                    break;
                                default:
                                    showErrorMessage(requestData.data.message);
                                    break;
                            }

                            var responseCallbackFn = window[responseCallback];
                            if (typeof responseCallbackFn === 'function') {
                                responseCallbackFn(requestData);
                            }
                        },
                    }
                },
                onprocessfile: function (error, file) {
                    if (removeMediaAfterUpload) {
                        pond.removeFile(file.id);
                    }
                    if (removeAllMediaAfterUpload) {
                        pond.removeFiles();
                    }
                }/* ,
                      onprocessfilerevert: function (file) {
                          __pr(error, file);
                      } */
            };

        if (typeof defaultImage != 'undefined' && !_.isEmpty(defaultImage)) {
            filePondAdditionalOptions = $.extend({}, filePondAdditionalOptions, {
                files: [
                    {
                        // set type to local to indicate an already uploaded file
                        options: {
                            type: 'local',
                            file: {
                                name: '',
                                size: uploader.size,
                                type: 'image/jpg'
                            },
                            // Pass Default Image Url
                            metadata: {
                                poster: defaultImage
                            }
                        }
                    }
                ]
            });
        }

        var pond = FilePond.create(this, filePondAdditionalOptions);
    });
};
fileUploaderInit();

var photoSwipeGallery = function (items, index) {

    //default index
    var index = parseInt(index);

    // default options
    var options = {
        index: index,
        history: false,
        focus: false,
        closeEl: true,
        captionEl: true,
        fullscreenEl: true,
        zoomEl: true,
        shareEl: false,
        counterEl: true,
        arrowEl: true,
        preloaderEl: true,
        tapToToggleControls: false,
        showAnimationDuration: 0,
        hideAnimationDuration: 0,
    };

    var gallery = new PhotoSwipe(document.querySelectorAll('.pswp')[0], PhotoSwipeUI_Default, items, options);
    gallery.init();

    // Gallery starts closing
    // Note: 05 JUN 2023 - as its creating an issue while closing on datatable we have fixed it using following trick
    if ($('body.lw-admin-section').length) {
        gallery.listen('destroy', function () {
            _.defer(function () {
                $('body.lw-admin-section .pswp').replaceWith(`<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                    <div class="pswp__counter"></div>
                    <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                    <button class="pswp__button pswp__button--share" title="Share"></button>
                    <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                    <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>
                <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                </button>
                <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                </button>
                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>`);
            });
        });
    };
}

//for handling photoswipe gallery
$(function () {

    $('.lw-page-content').on('click', '.lw-datatable-photoswipe-gallery', function (event) {

        var items;
        var index = 0;

        if ($(event.target).hasClass('lw-photoswipe-gallery-img')) {
            // for fetching  all imgs url
            items = [{
                'src': $(event.target).attr('src'),
                'w': 900,
                'h': 900
            }];

            photoSwipeGallery(items, index);
        }
    });

    $('.lw-page-content').on('click', '.lw-photoswipe-gallery-img', function (event) {
        var siblings = $(this).siblings('.lw-photoswipe-gallery-img').addBack();
        var items;
        var index = 0;

        if (siblings.length > 0) {
            items = siblings.map(function (index, elem) {
                return {
                    'src': $(elem).attr('src'),
                    'w': 900,
                    'h': 900
                }
            });

            //if index is set
            if ($(event.target).data('img-index')) {
                index = $(event.target).data('img-index');
            }

            // if items not empty
            if (items.length > 0) {
                photoSwipeGallery(items, index);
            }
        } else {
            items = [{
                'src': $(event.target).attr('src'),
                'w': 900,
                'h': 900
            }];
            // if items not empty
            if (items.length > 0) {
                photoSwipeGallery(items, index);
            }
        }
    });
});

var applyLazyImages = function () {
    $(".lw-lazy-img").Lazy({
        // effect: "fadeIn",
        // effectTime: 200,
        // threshold: 0,
        beforeLoad: function ($element) {
            // called before an elements gets handled
            // $element.addClass('lw-lazy-img-loading');
        },
        afterLoad: function ($element) {
            // called after an element was successfully handled
            $element.addClass('lw-lazy-img-loaded');
            //    $element.removeClass('lw-lazy-img-loading');
        },
        onError: function ($element) {
            $element.addClass('lw-lazy-img-error');
            // $element.removeClass('lw-lazy-img-loading');
            console.log('error loading ' + $element.data('src'));
        }
    });
}

$(function () {
    applyLazyImages();
});

$(document).on('lw_events_ajax_success', function (e, options) {
    var response = options.response;
    if (_.get(response, 'data.auth_info.authorized') == false) {
        __Utils.viewReload();
    } else if ((response.data != '') && (_.isString(response.data) || (response.response_action && (response.response_action.target != '.lw-page-content')))) {
        $("#lwNextPageLink").remove();
        $("#lwLoadMoreContentContainer").append(response.data);
    }
    applyLazyImages();
});

// Find users load more event callback function
$(document).on('onLoadMoreFilterUsers', function (e, options) {
    var responseData = options.response,
        requestData = responseData.data,
            appendData = responseData.response_action.content;
        $('#lwUserFilterContainer').append(appendData);
        $('#lwLoadMoreButton').data('action', requestData.nextPageUrl);
        if (!requestData.hasMorePages) {
            $('.lw-load-more-container').hide();
            $('#lwLoadMoreResultMessage').show();
        }
        applyLazyImages();
});

/* $('.lw-public-master .sidebar li.nav-item').on('click', function (e) {
    $('.lw-public-master .sidebar .nav-item').removeClass('active');
    $(this).addClass('active');
}); */

/* $('.lw-admin-section .sidebar .nav-item:not(.lw-settings-sub-menu-items)').on('click', function (e) {
    $('.lw-admin-section .sidebar .nav-item').removeClass('active');
    $('.lw-admin-section .sidebar .nav-item .nav-link').removeClass('active');
    $(this).addClass('active');
}); */

$('.sidebar .nav-item .nav-link, .lw-public-master .sidebar li.nav-item').on('click', function (e) {
    $('.sidebar .nav-item, .sidebar .nav-item .nav-link').removeClass('active');
    $(this).parents('.nav-item.lw-settings-sub-menu-items').find('.nav-link').first().addClass('active');
    $(this).addClass('active');
});

var showConfirmation = function (containerId, yesCallback, options, confirmParams) {

    var $messageItem = (!_.includes(containerId, ' ')) ? $(containerId) : false,
        confirmationContainer = '';

    if ($messageItem && $messageItem.length) {
        confirmationContainer = _.template($messageItem.html());
    } else {
        confirmationContainer = containerId;
    }
    if (!options) {
        options = {};
    }

    options = _.assign({
        showCancelBtn: true,
        type: 'warning',
        confirmBtnColor: '#d33d33',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes',
        background: 'white',
        cancelBtnColor: '#6e7881',
        popup: '',
        html:'',
        title:'',
        showDenyButton: false,
        denyButtonText: "Don't save",
    }, options);

    if (!confirmParams) {
        confirmParams = {};
    }

    Swal.fire({
        // title: 'Are you sure?',
        title: options['title'],
        html: _.isString(confirmationContainer) ? confirmationContainer : confirmationContainer(confirmParams),
        icon: options['type'],
        confirmButtonText: options['confirmButtonText'],
        cancelButtonColor: options['cancelBtnColor'],
        cancelButtonText: options['cancelButtonText'],
        confirmButtonColor: options['confirmBtnColor'], // 3085d6
        showCancelButton: options['showCancelBtn'],
        background: options['background'],
        showDenyButton: options['showDenyButton'],
        denyButtonText: options['denyButtonText'],
        background: '#333',
        // popup: 'dark-theme',
        customClass: {
            popup: options['popup'],
        },
    }).then(function (result) {
        if (result.isConfirmed) {
            yesCallback();
        }
    });
};

window.getPageMetaData = function (item) {
    return $('script[data-'+item+']').data(item);
}

$(document).on('lwPrepareUploadPlugIn', function (e, options) {
    fileUploaderInit();
});

// as state is set programmatically for lw-ajax-action using the url change we needs to force back/forward button work.
/* $(window).on('popstate', function (event) {
    __pr(event.target);
    // location.reload();
}); */