'use strict';

(function($) {
  var woocr_timeout = null;

  $(function() {
    // hide search result box by default
    $('#woocr_results').hide();
    $('#woocr_loading').hide();

    // arrange
    woocr_arrange();

    // smart related
    woocr_source_init();
    woocr_build_label();
    woocr_terms_init();
    woocr_enhanced_select();
    woocr_combination_terms_init();
    woocr_sortable();
  });

  // search input
  $(document).on('keyup', '#woocr_keyword', function() {
    if ($('#woocr_keyword').val() != '') {
      $('#woocr_loading').show();

      if (woocr_timeout != null) {
        clearTimeout(woocr_timeout);
      }

      woocr_timeout = setTimeout(woocr_ajax_get_data, 300);
      return false;
    }
  });

  // actions on search result items
  $(document).on('click touch', '#woocr_results li', function() {
    $(this).find('input').attr('name', 'woocr_ids[]');
    $(this).find('.woocr-remove').attr('aria-label', 'Remove').html('×');
    $('#woocr_selected ul').append($(this));
    $('#woocr_results').hide();
    $('#woocr_keyword').val('');
    woocr_arrange();
    return false;
  });

  // actions on selected items
  $(document).on('click touch', '#woocr_selected .woocr-remove', function() {
    $(this).parent().remove();
    return false;
  });

  // hide search result box if click outside
  $(document).on('click touch', function(e) {
    if ($(e.target).closest($('#woocr_results')).length == 0) {
      $('#woocr_results').hide();
    }
  });

  function woocr_arrange() {
    $('#woocr_selected ul').sortable({
      handle: '.move',
    });
  }

  function woocr_ajax_get_data() {
    // ajax search product
    var ids = [];

    woocr_timeout = null;

    $('input[name="woocr_ids[]"]').each(function() {
      ids.push($(this).val());
    });

    var data = {
      action: 'woocr_get_search_results',
      keyword: $('#woocr_keyword').val(),
      ids: ids,
    };

    $.post(ajaxurl, data, function(response) {
      $('#woocr_results').show();
      $('#woocr_results').html(response);
      $('#woocr_loading').hide();
    });
  }

  $(document).on('change', '.woocr_source_selector', function() {
    var $this = $(this);
    var type = $this.data('type');
    var $rule = $this.closest('.woocr_rule');

    woocr_source_init(type, $rule);
    woocr_build_label($rule);
    woocr_terms_init();
  });

  $(document).on('change', '.woocr_terms', function() {
    var $this = $(this);
    var type = $this.data('type');
    var apply = $(this).
        closest('.woocr_rule').
        find('.woocr_source_selector_' + type).
        val();

    $this.data(apply, $this.val().join());
  });

  $(document).on('change', '.woocr_combination_selector', function() {
    woocr_combination_terms_init();
  });

  $(document).on('click touch', '.woocr_combination_remove', function() {
    $(this).closest('.woocr_combination').remove();
  });

  $(document).on('click touch', '.woocr_rule_heading', function(e) {
    if ($(e.target).closest('.woocr_rule_remove').length === 0 &&
        $(e.target).closest('.woocr_rule_duplicate').length === 0) {
      $(this).closest('.woocr_rule').toggleClass('active');
    }
  });

  $(document).on('click touch', '.woocr_new_combination', function(e) {
    var $combinations = $(this).
        closest('.woocr_tr').
        find('.woocr_combinations');
    var key = $(this).
        closest('.woocr_rule').data('key');
    var name = $(this).data('name');
    var type = $(this).data('type');
    var data = {
      action: 'woocr_add_combination',
      nonce: woocr_vars.woocr_nonce,
      key: key,
      name: name,
      type: type,
    };

    $.post(ajaxurl, data, function(response) {
      $combinations.append(response);
      woocr_combination_terms_init();
    });

    e.preventDefault();
  });

  $(document).on('click touch', '.woocr_new_rule', function(e) {
    e.preventDefault();
    $('.woocr_rules').addClass('woocr_rules_loading');

    var name = $(this).data('name');
    var data = {
      action: 'woocr_add_rule', nonce: woocr_vars.woocr_nonce, name: name,
    };

    $.post(ajaxurl, data, function(response) {
      $('.woocr_rules').append(response);
      woocr_source_init();
      woocr_build_label();
      woocr_terms_init();
      woocr_enhanced_select();
      woocr_combination_terms_init();
      $('.woocr_rules').removeClass('woocr_rules_loading');
    });
  });

  $(document).on('click touch', '.woocr_rule_duplicate', function(e) {
    e.preventDefault();
    $('.woocr_rules').addClass('woocr_rules_loading');

    var $rule = $(this).closest('.woocr_rule');
    var rule_data = $rule.find('input, select, button, textarea').
        serialize() || 0;
    var name = $(this).data('name');
    var data = {
      action: 'woocr_add_rule',
      nonce: woocr_vars.woocr_nonce,
      name: name,
      rule_data: rule_data,
    };

    $.post(ajaxurl, data, function(response) {
      $(response).insertAfter($rule);
      woocr_source_init();
      woocr_build_label();
      woocr_terms_init();
      woocr_enhanced_select();
      woocr_combination_terms_init();
      $('.woocr_rules').removeClass('woocr_rules_loading');
    });
  });

  $(document).on('click touch', '.woocr_rule_remove', function(e) {
    e.preventDefault();

    if (confirm('Are you sure?')) {
      $(this).closest('.woocr_rule').remove();
    }
  });

  $(document).on('click touch', '.woocr_expand_all', function(e) {
    e.preventDefault();

    $('.woocr_rule').addClass('active');
  });

  $(document).on('click touch', '.woocr_collapse_all', function(e) {
    e.preventDefault();

    $('.woocr_rule').removeClass('active');
  });

  $(document).on('click touch', '.woocr_conditional_remove', function(e) {
    e.preventDefault();

    if (confirm('Are you sure?')) {
      $(this).closest('.woocr_conditional_item').remove();
    }
  });

  function woocr_terms_init() {
    $('.woocr_terms').each(function() {
      var $this = $(this);
      var type = $this.data('type');
      var apply = $this.closest('.woocr_rule').
          find('.woocr_source_selector_' + type).
          val();

      $this.selectWoo({
        ajax: {
          url: ajaxurl, dataType: 'json', delay: 250, data: function(params) {
            return {
              q: params.term, action: 'woocr_search_term', taxonomy: apply,
            };
          }, processResults: function(data) {
            var options = [];
            if (data) {
              $.each(data, function(index, text) {
                options.push({id: text[0], text: text[1]});
              });
            }
            return {
              results: options,
            };
          }, cache: true,
        }, minimumInputLength: 1,
      });

      if (apply !== 'all' && apply !== 'products' && apply !== 'combination') {
        // for terms only
        if ((typeof $this.data(apply) === 'string' ||
            $this.data(apply) instanceof String) && $this.data(apply) !== '') {
          $this.val($this.data(apply).split(',')).change();
        } else {
          $this.val([]).change();
        }
      }
    });
  }

  function woocr_combination_terms_init() {
    $('.woocr_apply_terms').each(function() {
      var $this = $(this);
      var taxonomy = $this.closest('.woocr_combination').
          find('.woocr_combination_selector').
          val();

      $this.selectWoo({
        ajax: {
          url: ajaxurl, dataType: 'json', delay: 250, data: function(params) {
            return {
              q: params.term, action: 'woocr_search_term', taxonomy: taxonomy,
            };
          }, processResults: function(data) {
            var options = [];
            if (data) {
              $.each(data, function(index, text) {
                options.push({id: text[0], text: text[1]});
              });
            }
            return {
              results: options,
            };
          }, cache: true,
        }, minimumInputLength: 1,
      });
    });

  }

  function woocr_source_init(type = 'apply', $rule) {
    if (typeof $rule !== 'undefined') {
      var apply = $rule.find('.woocr_source_selector_' + type).
          find(':selected').
          val();
      var text = $rule.find('.woocr_source_selector_' + type).
          find(':selected').
          text();

      $rule.find('.woocr_' + type + '_text').text(text);
      $rule.find('.hide_' + type).hide();
      $rule.find('.show_if_' + type + '_' + apply).show();
      $rule.find('.show_' + type).show();
      $rule.find('.hide_if_' + type + '_' + apply).hide();
    } else {
      $('.woocr_source_selector').each(function(e) {
        var type = $(this).data('type');
        var $rule = $(this).closest('.woocr_rule');
        var apply = $(this).find(':selected').val();
        var text = $(this).find(':selected').text();

        $rule.find('.woocr_' + type + '_text').text(text);
        $rule.find('.hide_' + type).hide();
        $rule.find('.show_if_' + type + '_' + apply).show();
        $rule.find('.show_' + type).show();
        $rule.find('.hide_if_' + type + '_' + apply).hide();
      });
    }
  }

  function woocr_sortable() {
    $('.woocr_rules').sortable({handle: '.woocr_rule_move'});
  }

  function woocr_enhanced_select() {
    $(document.body).trigger('wc-enhanced-select-init');
  }

  function woocr_build_label($rule) {
    if (typeof $rule !== 'undefined') {
      var apply = $rule.find('.woocr_source_selector_apply').
          find('option:selected').
          text();
      var get = $rule.find('.woocr_source_selector_get').
          find('option:selected').
          text();

      $rule.find('.woocr_rule_label').
          html('Apply for: ' + apply + ' <span>Linked products: ' + get +
              '</span>');
    } else {
      $('.woocr_rule ').each(function() {
        var $this = $(this);
        var apply = $this.find('.woocr_source_selector_apply').
            find('option:selected').
            text();
        var get = $this.find('.woocr_source_selector_get').
            find('option:selected').
            text();

        $this.find('.woocr_rule_label').
            html('Apply for: ' + apply + ' <span>Linked products: ' + get +
                '</span>');
      });
    }
  }
})(jQuery);