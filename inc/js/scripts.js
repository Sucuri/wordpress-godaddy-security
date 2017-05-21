/* global jQuery */
/* jshint unused:false */

function gddysecAlertClose (id) {
    var a = document.getElementById('gddysec-alert-' + id);
    a.parentNode.removeChild(a);
}

jQuery(document).ready(function ($) {
    $('.gddysec-modal-btn').on('click', function (event) {
        event.preventDefault();

        var modalid = $(this).data('modalid');

        $('div.' + modalid).removeClass('gddysec-hidden');
    });

    $('.gddysec-overlay, .gddysec-modal-close').on('click', function(event) {
        event.preventDefault();

        $('.gddysec-overlay').addClass('gddysec-hidden');
        $('.gddysec-modal').addClass('gddysec-hidden');
    });

    if ($('.gddysec-tabs').length) {
        var d = 'gddysec-hidden';
        var b = 'gddysec-tab-active';
        var a = location.href.split('#')[1];

        $('.gddysec-tabs > ul a').on('click', function (event) {
            event.preventDefault();

            var tabbtn = $(this);
            var tabname = tabbtn.data('tabname');
            var f = $('.gddysec-tab-containers > #gddysec-' + tabname);

            if (f.length) {
                var g = location.href.replace(location.hash, '');
                var i = g + '#' + tabname;

                window.history.pushState({}, document.title, i);

                $('.gddysec-tabs > ul a').removeClass(b);
                $('.gddysec-tab-containers > div').addClass(d);

                tabbtn.addClass(b);
                f.removeClass(d);
            }
        });

        $('.gddysec-tab-containers > div').addClass(d);

        if (a !== undefined) {
            $('.gddysec-tabs > ul li a').each(function(e, f) {
                if ($(f).data('tabname') === a) {
                    $(f).trigger('click');
                }
            });
        } else {
            $('.gddysec-tabs > ul li:first-child a').trigger('click');
        }
    }

    $('body').on('click', '.gddysec-reveal', function (event) {
        event.preventDefault();

        var target = $(this).attr('data-target');
        $('.gddysec-' + target).removeClass('gddysec-hidden');
    });

    $('body').on('click', '.gddysec-corefiles .manage-column :checkbox', function () {
        $('.gddysec-corefiles tbody :checkbox').each(function(key, element) {
            var checked = $(element).is(':checked');
            $(element).attr('checked', !checked);
        });
    });
});
