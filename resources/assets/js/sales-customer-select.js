/**
 * POS customer Select2: show 50 preloaded options on open, AJAX search for the rest.
 */
'use strict';

(function ($) {
  function cleanup($el) {
    if ($el.hasClass('select2-hidden-accessible') || $el.data('select2')) {
      try {
        $el.select2('destroy');
      } catch (e) {
        // ignore
      }
    }
    $el.siblings('.select2-container').remove();
    $el.next('.select2-container').remove();
    var $parent = $el.parent();
    if ($parent.hasClass('position-relative')) {
      $parent.children('.select2-container').remove();
      if ($parent.children().length === 1 && $parent.children()[0] === $el[0]) {
        $el.unwrap();
      }
    }
  }

  function optionToResult($opt) {
    return {
      id: $opt.val(),
      text: $.trim($opt.text()),
      name: $opt.data('name') || $.trim($opt.text()),
      email: $opt.data('email') || '',
      phone: $opt.data('phone') || '',
      image: $opt.data('image') || '',
      branch_id: $opt.data('branch-id') || ''
    };
  }

  function localResults($el) {
    var results = [];
    $el.find('option').each(function () {
      var $opt = $(this);
      if (!$opt.val()) return;
      results.push(optionToResult($opt));
    });
    return results;
  }

  function stampOption($select, data) {
    if (!data || !data.id) return;
    var $opt = $select.find('option[value="' + data.id + '"]');
    if (!$opt.length) {
      $opt = $('<option></option>').val(data.id).text(data.text || data.name || data.id);
      $select.append($opt);
    }
    $opt.attr('data-name', data.name || $opt.data('name') || '');
    $opt.attr('data-email', data.email || $opt.data('email') || '');
    $opt.attr('data-phone', data.phone || data.full_phone || $opt.data('phone') || '');
    $opt.attr('data-image', data.image || $opt.data('image') || '');
    $opt.attr('data-branch-id', data.branch_id || $opt.data('branch-id') || '');
  }

  function initOne($el) {
    if (!$el.length) {
      return;
    }

    cleanup($el);

    var parentSelector = $el.data('dropdown-parent');
    var $parent = parentSelector ? $(parentSelector) : $el.parent();
    var searchUrl = $el.data('search-url');
    var placeholder = $el.data('placeholder') || '';
    var noResults = $el.data('no-results') || '';
    var searching = $el.data('searching') || '';

    $el.select2({
      width: '100%',
      dropdownParent: $parent.length ? $parent : $(document.body),
      placeholder: placeholder,
      allowClear: true,
      minimumInputLength: 0,
      ajax: {
        delay: 250,
        dataType: 'json',
        transport: function (params, success, failure) {
          var term = $.trim((params.data && params.data.q) || '');
          if (!term) {
            success({
              results: localResults($el),
              pagination: { more: false }
            });
            return;
          }

          $.ajax({
            url: searchUrl,
            dataType: 'json',
            data: { q: term, page: (params.data && params.data.page) || 1 },
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              Accept: 'application/json'
            }
          })
            .done(success)
            .fail(failure);
        },
        data: function (params) {
          return { q: params.term || '', page: params.page || 1 };
        },
        processResults: function (data) {
          return {
            results: data && data.results ? data.results : [],
            pagination: data && data.pagination ? data.pagination : { more: false }
          };
        },
        cache: true
      },
      language: {
        noResults: function () {
          return noResults;
        },
        searching: function () {
          return searching;
        }
      }
    });

    $el.off('select2:select.stamp').on('select2:select.stamp', function (e) {
      stampOption($el, e.params.data);
    });
  }

  var booted = false;
  function boot() {
    if (booted) return;
    booted = true;
    $('.js-customer-select').each(function () {
      initOne($(this));
    });
  }

  $(window).on('load', function () {
    setTimeout(boot, 0);
  });
  if (document.readyState === 'complete') {
    setTimeout(boot, 0);
  }
})(jQuery);
