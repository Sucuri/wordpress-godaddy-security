<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Lists the logs collected by the API service.
 */
class GddysecAuditLogs
{
    /**
     * Print a HTML code with the content of the logs audited by the remote Sucuri
     * API service, this page is part of the monitoring tool.
     *
     * @return void
     */
    public static function pageAuditLogs()
    {
        $params = array();
        $params['PageTitle'] = 'Audit Logs';

        // Skip audit logs retrieval if there is no API key.
        if (!GddysecOption::get_option(':api_key')) {
            return '' /* return empty page */;
        }

        return GddysecTemplate::getSection('integrity-auditlogs', $params);
    }

    public static function ajaxAuditLogs()
    {
        if (GddysecRequest::post('form_action') !== 'get_audit_logs') {
            return;
        }

        $response = array();
        $response['count'] = 0;
        $response['content'] = '';
        $response['enable_report'] = false;

        // Initialize the values for the pagination.
        $max_per_page = GDDYSEC_AUDITLOGS_PER_PAGE;
        $page_number = GddysecTemplate::pageNumber();
        $logs_limit = ($page_number * $max_per_page);

        // Get data from the cache if possible.
        $errors = ''; /* no errors so far */
        $cache = new GddysecCache('auditlogs');
        $auditlogs = $cache->get('response', GDDYSEC_AUDITLOGS_LIFETIME, 'array');

        // API call if cache is invalid.
        if (!$auditlogs) {
            ob_start();
            $auditlogs = GddysecAPI::getAuditLogs($logs_limit);
            $errors = ob_get_contents();
            ob_end_clean();
        }

        // Stop everything and report errors.
        if (!empty($errors)) {
            header('Content-Type: text/html; charset=UTF-8');
            print($errors);
            exit(0);
        }

        // Cache the data for sometime.
        if ($auditlogs && empty($errors)) {
            $cache->add('response', $auditlogs);
        }

        if ($auditlogs) {
            $counter_i = 0;
            $total_items = count($auditlogs['output_data']);
            $iterator_start = ($page_number - 1) * $max_per_page;

            if (array_key_exists('total_entries', $auditlogs)
                && $auditlogs['total_entries'] >= $max_per_page
                && GddysecOption::is_disabled(':audit_report')
            ) {
                $response['enable_report'] = true;
            }

            for ($i = $iterator_start; $i < $total_items; $i++) {
                if ($counter_i > $max_per_page) {
                    break;
                }

                if (!isset($auditlogs['output_data'][$i])) {
                    continue;
                }

                $audit_log = $auditlogs['output_data'][ $i ];

                $css_class = ($counter_i%2 === 0) ? '' : 'alternate';
                $snippet_data = array(
                    'AuditLog.CssClass' => $css_class,
                    'AuditLog.Event' => $audit_log['event'],
                    'AuditLog.EventTitle' => ucfirst($audit_log['event']),
                    'AuditLog.Timestamp' => $audit_log['timestamp'],
                    'AuditLog.DateTime' => Gddysec::datetime($audit_log['timestamp']),
                    'AuditLog.Account' => $audit_log['account'],
                    'AuditLog.Username' => $audit_log['username'],
                    'AuditLog.RemoteAddress' => $audit_log['remote_addr'],
                    'AuditLog.Message' => $audit_log['message'],
                    'AuditLog.Extra' => '',
                );

                // Print every file_list information item in a separate table.
                if ($audit_log['file_list']) {
                    $css_scrollable = $audit_log['file_list_count'] > 10 ? 'gddysec-list-as-table-scrollable' : '';
                    $snippet_data['AuditLog.Extra'] .= '<ul class="gddysec-list-as-table ' . $css_scrollable . '">';

                    foreach ($audit_log['file_list'] as $log_extra) {
                        $snippet_data['AuditLog.Extra'] .= '<li>' . Gddysec::escape($log_extra) . '</li>';
                    }

                    $snippet_data['AuditLog.Extra'] .= '</ul>';
                }

                $response['content'] .= GddysecTemplate::getSnippet('integrity-auditlogs', $snippet_data);
                $counter_i += 1;
            }

            $response['count'] = $counter_i;

            if ($total_items > 1) {
                $max_pages = ceil($auditlogs['total_entries'] / $max_per_page);

                if ($max_pages > GDDYSEC_MAX_PAGINATION_BUTTONS) {
                    $max_pages = GDDYSEC_MAX_PAGINATION_BUTTONS;
                }

                if ($max_pages > 1) {
                    $response['pagination'] = GddysecTemplate::pagination(
                        GddysecTemplate::getUrl(),
                        ($max_per_page * $max_pages),
                        $max_per_page
                    );
                }
            }
        }

        header('Content-Type: application/json');
        print(json_encode($response));
        exit(0);
    }
}
