jQuery(document).ready(function ($) {
    $(document).on('change', '#wpdcom input[name="wc_email"]', function (event) {
        var email = $('#wpdcom input[name="wc_email"]').val();
        var name = email.split('@')[0];
        $('#wpdcom input[name="wc_name"]').val(name);
    });

    $('#wpdcom .wpd_main_comm_form').on('submit', function (event) {
        var email = $('input[name="wc_email"]').val();
        var name = email.split('@')[0];
        $('input[name="wc_name"]').val(name);
    });

    $(window).on('load', function () {
        if (typeof wpdiscuzAjaxObj === 'undefined') {
            return;
        }
        var originalGetAjaxObj = wpdiscuzAjaxObj.getAjaxObj;
        wpdiscuzAjaxObj.getAjaxObj = function (secure, cache, data) {
            var ajaxObj = originalGetAjaxObj.call(this, secure, cache, data);
            var actionMethod = data.get("action");
            ajaxObj.then(function (response) {
                if (response.success) {
                    if ($('.wmu-tabs.wmu-mtestv2-tab').length == 0) {
                        $('.wmu-tabs.wmu-images-tab').after('<div class="wmu-tabs wmu-mtestv2-tab wmu-hide"></div>');
                    }
                    if (this.data) {
                        if (this.data.get('action') == 'wmuRemoveAttachmentPreview') {
                            return;
                        }
                    }
                    if (response.data.previewsData && response.data.previewsData.videos) {
                        $(".wmu-tabs.wmu-mtestv2-tab").html('');
                        for (var key in response.data.previewsData.videos) {
                            var o = response.data.previewsData.videos[key];
                            var d = o.id
                                , m = o.url
                                , i = o.fullname
                                , n = o.shortname
                                , a = 'videos';
                            var videoFrame = '<iframe class="wmu-preview-img" src="[PREVIEW_URL]" sandbox="allow-downloads: false"></iframe>';
                            if (m && m.toLowerCase().indexOf('.mov') >= 0) {
                                videoFrame = '<figure class="wp-block-video wmu-preview-img"><video width="320" height="240" controls="" src="[PREVIEW_URL]"></video> </figure>';
                            }
                            var s = '<div class="wmu-preview [PREVIEW_TYPE_CLASS]" title="[PREVIEW_TITLE]" data-wmu-type="[PREVIEW_TYPE]" data-wmu-attachment="[PREVIEW_ID]" filename="[PREVIEW_FILENAME]"><div class="wmu-preview-remove">' + videoFrame + '<div class="wmu-delete">&nbsp;</div></div></div>';
                            s = (s = (s = (s = (s = (s = s.replace("[PREVIEW_TYPE_CLASS]", "wmu-preview-videos")).replace("[PREVIEW_TITLE]", i)).replace("[PREVIEW_TYPE]", a)).replace("[PREVIEW_ID]", d)).replace("[PREVIEW_URL]", m)).replace("[PREVIEW_FILENAME]", n),
                                $(".wmu-tabs.wmu-mtestv2-tab").removeClass("wmu-hide").append(s);
                        }
                    }
                }
                console.log("Custom logic on successful Ajax call.");
            });
            return ajaxObj;
        };
    });
});