
%%%GDDYSEC.Integrity%%%

<script type="text/javascript">
/* global jQuery */
/* jshint camelcase: false */
jQuery(document).ready(function ($) {
    var gddysecSiteCheckLinks = function (target, links) {
        if (links.length === 0) {
            $(target).html('<div><em>no data available</em></div>');
            return;
        }

        var tbody = $('<tbody>');
        var options = {class: 'wp-list-table widefat gddysec-table'};

        for (var key in links) {
            if (links.hasOwnProperty(key)) {
                tbody.append('<tr><td><a href="' + links[key] + '" target="_b' +
                'lank" class="gddysec-monospace">' + links[key] + '</a></t' +
                'd></tr>');
            }
        }

        $(target).html($('<table>', options).html(tbody));
    };

    $.post('%%GDDYSEC.AjaxURL.Dashboard%%', {
        action: 'gddysec_ajax',
        gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
        form_action: 'malware_scan',
    }, function (data) {
        $('#gddysec-title-iframes').html(data.iframes.title);
        $('#gddysec-title-links').html(data.links.title);
        $('#gddysec-title-scripts').html(data.scripts.title);

        gddysecSiteCheckLinks('#gddysec-tabs-iframes', data.iframes.content);
        gddysecSiteCheckLinks('#gddysec-tabs-links', data.links.content);
        gddysecSiteCheckLinks('#gddysec-tabs-scripts', data.scripts.content);

        $('#gddysec-malware').html(data.malware);
        $('#gddysec-blacklist').html(data.blacklist);
        $('#gddysec-recommendations').html(data.recommendations);
    });
});
</script>

<div class="gddysec-clearfix">
    <div class="gddysec-pull-left gddysec-dashboard-left">
        <div class="gddysec-panel">
            %%%GDDYSEC.AuditLogs%%%
        </div>
    </div>

    <div class="gddysec-pull-right gddysec-dashboard-right">
        %%%GDDYSEC.SiteCheck.Malware%%%

        %%%GDDYSEC.SiteCheck.Blacklist%%%

        %%%GDDYSEC.SiteCheck.Recommendations%%%
    </div>
</div>
