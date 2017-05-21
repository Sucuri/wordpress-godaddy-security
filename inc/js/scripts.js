/* global jQuery */
/* jshint unused:false */

function gddysecAlertClose (id) {
    var element = document.getElementById('gddysec-alert-' + id);
    element.parentNode.removeChild(element);
}

jQuery(document).ready(function ($) {
    $('.gddysec-container').on('click', '.gddysec-modal-button', function (event) {
        event.preventDefault();
        var modalid = $(this).data('modalid');
        $('div.' + modalid + '-modal').removeClass('gddysec-hidden');
    });

    $('.gddysec-container').on('click', '.gddysec-overlay, .gddysec-modal-close', function (event) {
        event.preventDefault();
        $('.gddysec-overlay').addClass('gddysec-hidden');
        $('.gddysec-modal').addClass('gddysec-hidden');
    });

    $('.gddysec-container').on('click', '.gddysec-show-more', function (event) {
        event.preventDefault();
        var button = $(this);
        var target = button.attr('data-target');
        var status = button.attr('data-status');
        if (status === 'more') {
            button.attr('data-status', 'less');
            $(target).removeClass('gddysec-hidden');
            button.find('.gddysec-show-more-title').html('Show Less Info');
        } else {
            button.attr('data-status', 'more');
            $(target).addClass('gddysec-hidden');
            button.find('.gddysec-show-more-title').html('Show More Info');
        }
    });

    if ($('.gddysec-tabs').length) {
        var hiddenState = 'gddysec-hidden';
        var visibleState = 'gddysec-visible';
        var activeState = 'gddysec-tab-active';
        var locationHash = location.href.split('#')[1];

        $('.gddysec-container').on('click', '.gddysec-tabs-buttons a', function (event) {
            event.preventDefault();

            var button = $(this);
            var uniqueid = button.attr('href').split('#')[1];

            if (uniqueid !== undefined) {
                var container = $('.gddysec-tabs-containers > #gddysec-tabs-' + uniqueid);

                if (container.length) {
                    var rawurl = location.href.replace(location.hash, '');
                    var newurl = rawurl + '#' + uniqueid;

                    window.history.pushState({}, document.title, newurl);

                    $('.gddysec-tabs-buttons a').removeClass(activeState);
                    $('.gddysec-tabs-containers > div').addClass(hiddenState);

                    button.addClass(activeState);
                    container.addClass(visibleState);
                    container.removeClass(hiddenState);
                }
            }
        });

        $('.gddysec-tabs-containers > div').addClass(hiddenState);

        if (locationHash !== undefined) {
            $('.gddysec-tabs-buttons a').each(function (e, button) {
                if ($(button).attr('href').split('#')[1] === locationHash) {
                    $(button).trigger('click');
                }
            });
        } else {
            $('.gddysec-tabs-buttons li:first-child a').trigger('click');
        }
    }

    $('.gddysec-container').on('mouseover', '.gddysec-tooltip', function (event) {
        var element = $(this);
        var content = element.attr('content');

        if (!content) {
            return;
        }

        /* create instance of tooltip container */
        var tooltip = $('<div>', { 'class': 'gddysec-tooltip-object' });

        if (element.attr('tooltip-width')) {
            var customWidth = element.attr('tooltip-width');
            tooltip.css('width', customWidth);
        }

        /* interpret HTML code as is; careful with XSS */
        if (element.attr('tooltip-html') === 'true') {
            tooltip.html(content);
        } else {
            tooltip.text(content);
        }

        element.append(tooltip);
        var arrowHeight = 10; /* border width */
        var tooltipHeight = tooltip.outerHeight();
        tooltip.css('top', (tooltipHeight + arrowHeight) * -1);

        var positionLeft = 0;
        var elementWidth = element.outerWidth();
        var tooltipWidth = tooltip.outerWidth();

        if (elementWidth === tooltipWidth) {
            tooltip.css('left', 0);
        } else if (elementWidth > tooltipWidth) {
            tooltip.css('left', (elementWidth - tooltipWidth) / 2);
        } else if (elementWidth < tooltipWidth) {
            tooltip.css('left', ((tooltipWidth - elementWidth) / 2) * -1);
        }
    });

    $('.gddysec-container').on('mouseout', '.gddysec-tooltip', function (event) {
        $(this).find('.gddysec-tooltip-object').remove();
    });
});
