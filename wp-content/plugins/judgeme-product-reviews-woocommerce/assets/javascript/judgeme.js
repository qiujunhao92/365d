jQuery(function ($) {
    jQuery(document).ready(function() {
        jdgmToggleWidgetsOnLoad()
    });

    if (jQuery('.judgeme__woocommerce-plugin-container').length > 0) {
        new ClipboardJS('.fa-clone');
    }

    if (jQuery('.judgeme-exporter').is('div')) {
        jQuery('.woocommerce-save-button').hide();

        if (window.location.href.includes('debug=1')) {
            jQuery('.toggle-advanced-debug').show();
        }
    }

    // Export function
    jQuery('.judgeme-exporter button, .judgeme-product-sync button').click(function () {
        jQuery('.judgeme-exporter .response').html('');
        jQuery(this).attr('disabled', 'disabled');
        jQuery(this).addClass('clicked');
        jgmSyncProducts(1);
    });

    jQuery('.clear-sync-btn').click(function(){
        jgmClearSync();
    });

    jQuery('.clear-sync-each-product-btn').click(function(){
        jgmClearSyncEachProduct();
    });

    jQuery('.toggle-advanced-debug').click(function(){
        jQuery('.advanced-debug').toggle(500);
    });

    jQuery('.clean-product-btn').click(function(){
        var productId = jQuery('#jgm-product-id').val();
        if (!isNaN(parseInt(productId))) {
            jgmCleanSingleProduct(parseInt(productId));
        } else {
            alert('Invalid product id');
        }
    });

    jQuery('.clear-shop-token-btn').click(function(){
        jgmClearShopToken();
    });

    function jdgmToggleWidgetsOnLoad() {
        var toggleElementsArray = jQuery('.home-page__quick-installation-product-page-badge-toggle-switch, .home-page__quick-installation-review-widget-toggle-switch, .home-page__quick-installation-collection-page-badge-toggle-switch, .advanced-tab__cache-clearing-widget-toggle-switch')

        jQuery.each(toggleElementsArray, function(i, toggleElement){
            jdgmToggleWidgetIcons(toggleElement)
        });
    }

    jQuery('.home-page__quick-installation-product-page-badge-toggle-switch, .home-page__quick-installation-review-widget-toggle-switch, .home-page__quick-installation-collection-page-badge-toggle-switch, .advanced-tab__cache-clearing-widget-toggle-switch').click(function(){
        var toggleType = jQuery(this).data('type');
        var request = {
            action: 'jgm_toggle_widget_placement',
            security: jQuery('.judgeme-exporter').data('nonce'),
            widget_type: toggleType
        };
        var toggleElement = this;

        jQuery.post(ajaxurl, request).done(function (data_raw) {
            jdgmToggleWidgetIcons(toggleElement)
        });
    });

    function jgmHandleReviews(page, total_pages) {
        var end = false;
        var request = {
            action: 'jgm_export_reviews',
            security: jQuery('.judgeme-exporter').data('nonce'),
            page: page
        };

        if (total_pages === 0) {
            end = true;
        }

        if (total_pages) {
            request['total_pages'] = total_pages;
            if (page > total_pages) {
                end = true;
            }
        }

        if (!end) {
            jQuery.post(ajaxurl, request).done(function (data_raw) {
                var data = JSON.parse(data_raw);
                jQuery('.judgeme-exporter .response').html("<div class='notice'>" + data.message + "</div>");
                jgmHandleReviews(data.next_page, data.total_pages);
            });

        } else {
            jQuery('.judgeme-exporter .response').append(jQuery('#jgm-csv-file'));
            jQuery('.judgeme-exporter .result').show();
            jQuery('#jgm-csv-file').wrap('<div class="updated"></div>').show();
        }
    }

    function jgmSyncProducts(page, total_pages) {
        var end = false;
        var perPage = jQuery('#jgm-per-page').val() || 200;
        var request = {
            action: 'jgm_sync_products',
            security: jQuery('.judgeme-exporter').data('nonce'),
            page: page,
            per_page: perPage
        };

        if (total_pages === 0) {
            end = true;
        }

        if (total_pages) {
            request['total_pages'] = total_pages;
            if (page > total_pages) {
                end = true;
            }
        }

        if (!end) {
            jQuery.post(ajaxurl, request).done(function (data_raw) {
                var data = JSON.parse(data_raw);
                jQuery('.home-page-sychronize-products-container .product-sync-response').html("<div class='notice'>" + data.message + "</div>");
                jgmSyncProducts(data.next_page, data.total_pages);
            });

        } else {
            if (jQuery('.judgeme-exporter button').hasClass('clicked')) {
                jgmHandleReviews(1);
            }

        }
    }

    function jgmClearSync() {
        var request = {
            action: 'jgm_clear_syncs',
            security: jQuery('.judgeme-exporter').data('nonce'),
        };

        jQuery.post(ajaxurl, request).done(function (data_raw) {
            jQuery('.home-page-sychronize-products-container .product-sync-response').html("<div class='notice'>" + data_raw + "</div>");
        });

    }

    function jgmClearSyncEachProduct() {
        var request = {
            action: 'jgm_clear_sync_each_product',
            security: jQuery('.judgeme-exporter').data('nonce'),
        };

        jQuery.post(ajaxurl, request).done(function (data_raw) {
            jQuery('.home-page-sychronize-products-container .product-sync-response').html("<div class='notice'>" + data_raw + "</div>");
        });
    }

    function jgmCleanSingleProduct(product_id) {
        var request = {
            action: 'jgm_clean_single_product',
            security: jQuery('.judgeme-exporter').data('nonce'),
            product_id: product_id
        };

        jQuery.post(ajaxurl, request).done(function (data_raw) {
            alert('Product cleaned.')
        });
    }

    function jgmClearShopToken() {
        var request = {
            action: 'jgm_clear_shop_token',
            security: jQuery('.judgeme-exporter').data('nonce'),
        };

        jQuery.post(ajaxurl, request).done(function (data_raw) {
            alert('Shop token resetted.')
        });
    }

    function jdgmToggleWidgetIcons(toggleElement) {
        var isClearCacheToggle = jQuery(toggleElement).hasClass('advanced-tab__cache-clearing-widget-toggle-switch widget-toggle')
        var textElement = jQuery(toggleElement).parent().parent().children().last()

        if (jQuery(toggleElement).is(":checked")) {
            var newText = isClearCacheToggle ? 'We will clear cache by default' : 'Installed';
            textElement.text(newText).css('color', '#339999')
        }
        else {
            var newText = isClearCacheToggle ? 'We will not clear cache' : 'Uninstalled';
            textElement.text(newText).css('color', '#637381')
        }
    }

    jQuery('.home-page__install-widget-install-widget-yourself-link-container').click(function() {
      jQuery('.caret').toggleClass('flip');
      jQuery('.home-page__install-widget-yourself-installation-guide-container').toggle();
    });

    jQuery('.home-page__customer-support-advanced-tab').click(function() {
      jQuery('.home-page__customer-support-tab').css('display', 'inline-block');
    });

    jQuery('.home-page__faq-icon').click(function(){
      var faqAnswer = jQuery(this).next().children()[1]
      var faqInfoContainer = jQuery(this).next()

      if (jQuery(this).hasClass('fa-plus-circle')) {
        jQuery(faqAnswer).show()
        faqInfoContainer.css('border-bottom', '1px solid #E0E0E0');
        jQuery(this).toggleClass('fa-minus-circle fa-plus-circle');
      }
      else {
        jQuery(faqAnswer).hide()
        faqInfoContainer.css('border-bottom', 'none');
        jQuery(this).toggleClass('fa-plus-circle fa-minus-circle ');
      }
    });
});
