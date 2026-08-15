/**
 * Customer Select2: list all users on open, AJAX search by name/phone/email.
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

  function triggerEmptyQuery($el) {
    var instance = $el.data('select2');
    if (!instance) return;

    var $search = instance.dropdown.$search || instance.$dropdown.find('.select2-search__field');
    if ($search.length && !$search.val()) {
      $search.trigger('input');
    }
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
        url: searchUrl,
        dataType: 'json',
        delay: 0,
        cache: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json'
        },
        data: function (params) {
          return {
            q: $.trim(params.term || ''),
            page: params.page || 1
          };
        },
        processResults: function (data) {
          return {
            results: data && data.results ? data.results : [],
            pagination: {
              more: !!(data && data.pagination && data.pagination.more)
            }
          };
        }
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

    $el.off('select2:open.list').on('select2:open.list', function () {
      setTimeout(function () {
        triggerEmptyQuery($el);
      }, 0);
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
