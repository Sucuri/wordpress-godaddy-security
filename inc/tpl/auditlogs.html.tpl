
<script type="text/javascript">
/* global jQuery */
/* jshint camelcase:false */
jQuery(document).ready(function ($) {
    var writeQueueSize = function (queueSize) {
        if (queueSize === 0) {
            $('.gddysec-auditlogs-sendlogs-response').html('');
            $('.gddysec-sendlogs-panel').addClass('gddysec-hidden');
        } else {
            var msg = '\x20logs in the queue\x20&mdash;\x20';
            $('.gddysec-auditlogs-sendlogs-response').html((queueSize).toString() + msg);
            $('.gddysec-sendlogs-panel').removeClass('gddysec-hidden');
        }
    };

    var gddysecLoadAuditLogs = function (page) {
        var url = '%%GDDYSEC.AjaxURL.Dashboard%%';

        if (page !== undefined && page > 0) {
            url += '&paged=' + page;
        }

        $('.gddysec-auditlog-response').html('<em>Loading...</em>');
        $('.gddysec-auditlog-status').html('Loading...');
        $('.gddysec-pagination-loading').html('Loading...');
        $('.gddysec-pagination-panel').addClass('gddysec-hidden');
        $('.gddysec-auditlog-footer').addClass('gddysec-hidden');

        $.post(url, {
            action: 'gddysec_ajax',
            gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
            form_action: 'get_audit_logs',
        }, function (data) {
            $('.gddysec-pagination-loading').html('');

            writeQueueSize(data.queueSize);

            $('.gddysec-auditlog-status').html(data.status);
            $('.gddysec-auditlog-footer').removeClass('gddysec-hidden');

            if (data.content !== undefined) {
                $('.gddysec-auditlog-response').html(data.content);

                if (data.pagination !== '') {
                    $('.gddysec-pagination-panel').removeClass('gddysec-hidden');
                    $('.gddysec-auditlog-table .gddysec-pagination').html(data.pagination);
                }
            } else if (typeof data === 'object') {
                $('.gddysec-auditlog-response').html(
                '<textarea class="gddysec-full-textarea">' +
                JSON.stringify(data) + '</textarea>');
                $('.gddysec-auditlog-table .gddysec-pagination').html('');
            } else {
                $('.gddysec-auditlog-response').html(data);
                $('.gddysec-auditlog-table .gddysec-pagination').html('');
            }
        });
    }

    setTimeout(function () {
        gddysecLoadAuditLogs(0);
    }, 100);

    $('.gddysec-auditlog-table').on('click', '.gddysec-pagination-link', function (event) {
        event.preventDefault();
        window.scrollTo(0, $('#gddysec-integrity-response').height() + 100);
        gddysecLoadAuditLogs($(this).attr('data-page'));
    });

    $('.gddysec-auditlog-table').on('click', '.gddysec-auditlogs-sendlogs', function (event) {
        event.preventDefault();

        $('.gddysec-sendlogs-panel').attr('content', '');
        $('.gddysec-auditlogs-sendlogs-response').html('Loading...');

        $.post('%%GDDYSEC.AjaxURL.Dashboard%%', {
            action: 'gddysec_ajax',
            gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
            form_action: 'auditlogs_send_logs',
        }, function (data) {
            gddysecLoadAuditLogs(0);

            setTimeout(function (){
                var tooltipContent =
                    'Total logs in the queue: {TTLLOGS}<br>' +
                    'Maximum execution time: {MAXTIME}<br>' +
                    'Successfully sent to the API: {SUCCESS}<br>' +
                    'Total request timeouts (failures): {FAILURE}<br>' +
                    'Total execution time: {ELAPSED} secs';
                $('.gddysec-sendlogs-panel')
                    .attr('content', tooltipContent
                    .replace('{MAXTIME}', data.maxtime)
                    .replace('{TTLLOGS}', data.ttllogs)
                    .replace('{SUCCESS}', data.success)
                    .replace('{FAILURE}', data.failure)
                    .replace('{ELAPSED}', data.elapsed)
                );
            }, 200);
        });
    });
});
</script>

<div class="gddysec-auditlog-table">
    <div class="gddysec-auditlog-response">
        <em>Loading...</em>
    </div>

    <div class="gddysec-clearfix gddysec-pagination-panel">
        <ul class="gddysec-pull-left gddysec-pagination">
            <!-- Populated via JavaScript -->
        </ul>

        <div class="gddysec-pull-right gddysec-pagination-loading">
            <!-- Populated via JavaScript -->
        </div>
    </div>

    <div class="gddysec-clearfix gddysec-auditlog-footer">
        <div class="gddysec-pull-left gddysec-hidden gddysec-tooltip
            gddysec-sendlogs-panel" tooltip-width="250" tooltip-html="true">
            <small class="gddysec-auditlogs-sendlogs-response"></small>
            <small><a href="#" class="gddysec-auditlogs-sendlogs">Send Logs</a></small>
        </div>

        <div class="gddysec-pull-right">
            <small class="gddysec-auditlog-status"></small>
        </div>
    </div>
</div>
