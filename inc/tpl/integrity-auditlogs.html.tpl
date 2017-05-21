
<script type="text/javascript">
jQuery(function ($) {
    var gddysecLoadAuditLogs = function (page, reset) {
        var url = '%%GDDYSEC.AjaxURL.Home%%';

        if (page !== undefined && page > 0) {
            url += '&paged=' + page;
        }

        if (reset === true) {
            var loading = '<tr><td colspan="5"><em>Loading...</em></td></tr>';
            $('.gddysec-auditlogs tfoot').addClass('gddysec-hidden');
            $('.gddysec-auditlogs tbody').html(loading);
        }

        $('.gddysec-pagination-loading').html('Loading...');

        $.post(url, {
            action: 'gddysec_ajax',
            gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
            form_action: 'get_audit_logs',
        }, function (data) {
            if (data.content !== undefined) {
                $('.gddysec-auditlogs tbody').html(data.content);
                $('.gddysec-pagination-loading').html('');
                $('.gddysec-auditlogs-count').html('(' + data.count + ' latest logs)');

                if (data.pagination !== undefined && data.pagination !== '') {
                    $('.gddysec-auditlogs tfoot').removeClass('gddysec-hidden');
                    $('.gddysec-auditlogs .gddysec-pagination').html(data.pagination);
                }

                if (data.enable_report) {
                    $('.gddysec-audit-report').removeClass('gddysec-hidden');
                }
            } else if (typeof data === 'object') {
                $('.sucuriscan-auditlogs tbody').html(
                '<tr><td colspan="5">' +
                '<textarea class="sucuriscan-full-textarea">' +
                JSON.stringify(data) +
                '</textarea></td></tr>');
            } else {
                $('.gddysec-auditlogs tbody').html(
                '<tr><td colspan="5">' + data + '</td></tr>');
            }
        });
    }

    setTimeout(function () {gddysecLoadAuditLogs(0, true)}, 100);

    $('.gddysec-auditlogs-reload').on('click', function (event) {
        event.preventDefault();
        gddysecLoadAuditLogs($(this).attr('data-page'), true);
    });

    $('.gddysec-auditlogs').on('click', '.gddysec-pagination-link', function (event) {
        event.preventDefault();
        gddysecLoadAuditLogs($(this).attr('data-page'));
    });
});
</script>

<div class="gddysec-boxshadow gddysec-auditlogs">
    <h3 class="gddysec-tag-title gddysec-tag-green">
        <span class="gddysec-auditlogs-title">Audit Logs</span>
        <span class="gddysec-auditlogs-count">(Loading...)</span>
    </h3>

    <table class="wp-list-table widefat gddysec-table">
        <tbody>
            <tr>
                <td colspan="2">
                    <em>Loading...</em>
                </td>
            </tr>
        </tbody>

        <tfoot>
            <td colspan="2">
                <div class="gddysec-clearfix">
                    <ul class="gddysec-pull-left gddysec-pagination">
                        <!-- Populated via JavaScript -->
                    </ul>

                    <div class="gddysec-pull-right gddysec-pagination-loading">
                        <!-- Populated via JavaScript -->
                    </div>
                </div>
            </td>
        </tfoot>
    </table>
</div>
