<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * System events, reports and actions.
 *
 * An event is an action or occurrence detected by the program that may be
 * handled by the program. Typically events are handled synchronously with the
 * program flow, that is, the program has one or more dedicated places where
 * events are handled, frequently an event loop. Typical sources of events
 * include the user; another source is a hardware device such as a timer. Any
 * program can trigger its own custom set of events as well, e.g. to communicate
 * the completion of a task. A computer program that changes its behavior in
 * response to events is said to be event-driven, often with the goal of being
 * interactive.
 *
 * @see https://en.wikipedia.org/wiki/Event_(computing)
 */
class GddysecEvent extends Gddysec
{
    /**
     * Schedule the task to run the first filesystem scan.
     *
     * @return void
     */
    public static function schedule_task($run_now = true)
    {
        $task_name = 'gddysec_scheduled_scan';

        if (!wp_next_scheduled($task_name)) {
            wp_schedule_event(time() + 10, 'twicedaily', $task_name);
        }

        if ($run_now === true) {
            // Execute scheduled task after five minutes.
            wp_schedule_single_event(time() + 300, $task_name);
        }
    }

    /**
     * Checks last time we ran to avoid running twice (or too often).
     *
     * @param  integer $runtime    When the filesystem scan must be scheduled to run.
     * @param  boolean $force_scan Whether the filesystem scan was forced by an administrator user or not.
     * @return boolean             Either TRUE or FALSE representing the success or fail of the operation respectively.
     */
    private static function verify_run($runtime = 0, $force_scan = false)
    {
        $option_name = ':runtime';
        $last_run = GddysecOption::get_option($option_name);
        $current_time = time();

        // The filesystem scanner can be disabled from the settings page.
        if (GddysecOption::is_disabled(':fs_scanner') && $force_scan === false) {
            return false;
        }

        // Check if the last runtime is too near the current time.
        if ($last_run && !$force_scan) {
            $runtime_diff = $current_time - $runtime;

            if ($last_run >= $runtime_diff) {
                return false;
            }
        }

        GddysecOption::update_option($option_name, $current_time);

        return true;
    }

    /**
     * Check whether the current WordPress version must be reported to the API
     * service or not, this is to avoid duplicated information in the audit logs.
     *
     * @return boolean TRUE if the current WordPress version must be reported, FALSE otherwise.
     */
    private static function report_site_version()
    {
        $option_name = ':site_version';
        $reported_version = GddysecOption::get_option($option_name);
        $wp_version = self::site_version();

        if ($reported_version != $wp_version) {
            GddysecEvent::report_info_event('WordPress version detected ' . $wp_version);
            GddysecOption::update_option($option_name, $wp_version);

            return true;
        }

        return false;
    }

    /**
     * Gather all the checksums (aka. file hashes) of this site, send them, and
     * analyze them using the Sucuri Monitoring service, this will generate the
     * audit logs for this site and be part of the integrity checks.
     *
     * @param  boolean $force_scan Whether the filesystem scan was forced by an administrator user or not.
     * @return boolean             TRUE if the filesystem scan was successful, FALSE otherwise.
     */
    public static function filesystem_scan($force_scan = false)
    {
        $minimum_runtime = GDDYSEC_MINIMUM_RUNTIME;

        if (self::verify_run($minimum_runtime, $force_scan)
            && class_exists('GddysecFileInfo')
            && GddysecAPI::getPluginKey()
        ) {
            self::report_site_version();

            $file_info = new GddysecFileInfo();
            $signatures = $file_info->get_directory_tree_md5(ABSPATH);

            if ($signatures) {
                $hashes_sent = GddysecAPI::sendHashes($signatures);

                if ($hashes_sent) {
                    GddysecOption::update_option(':runtime', time());
                    return true;
                } else {
                    GddysecInterface::error('The file hashes could not be stored.');
                }
            } else {
                GddysecInterface::error('The file hashes could not be retrieved, the filesystem scan failed.');
            }
        }

        return false;
    }

    /**
     * Generates an audit event log (to be sent later).
     *
     * @param  integer $severity Importance of the event that will be reported, values from one to five.
     * @param  string  $message  The explanation of the event.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           TRUE if the event was logged in the monitoring service, FALSE otherwise.
     */
    private static function report_event($severity = 0, $message = '', $internal = false)
    {
        $user = wp_get_current_user();
        $username = false;
        $remote_ip = self::remoteAddr();

        // Identify current user in session.
        if ($user instanceof WP_User
            && isset($user->user_login)
            && !empty($user->user_login)
        ) {
            if ($user->user_login != $user->display_name) {
                $username = sprintf("\x20%s (%s),", $user->display_name, $user->user_login);
            } else {
                $username = sprintf("\x20%s,", $user->user_login);
            }
        }

        // Fixing severity value.
        $severity = (int) $severity;

        // Convert the severity number into a readable string.
        switch ($severity) {
            case 0:
                $severity_name = 'Debug';
                break;
            case 1:
                $severity_name = 'Notice';
                break;
            case 2:
                $severity_name = 'Info';
                break;
            case 3:
                $severity_name = 'Warning';
                break;
            case 4:
                $severity_name = 'Error';
                break;
            case 5:
                $severity_name = 'Critical';
                break;
            default:
                $severity_name = 'Info';
                break;
        }

        // Mark the event as internal if necessary.
        if ($internal === true) {
            $severity_name = '@' . $severity_name;
        }

        // Clear event message.
        $message = strip_tags($message);
        $message = str_replace("\r", '', $message);
        $message = str_replace("\n", '', $message);
        $message = str_replace("\t", '', $message);

        $event_message = sprintf(
            '%s:%s %s; %s',
            $severity_name,
            $username,
            $remote_ip,
            $message
        );

        return self::sendEventLog($event_message);
    }

    public static function sendEventLog($event_message = '')
    {
        if (GddysecOption::is_enabled(':api_service')) {
            GddysecAPI::sendLogsFromQueue();

            return GddysecAPI::sendLog($event_message);
        }

        return true;
    }

    /**
     * Reports a debug event on the website.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_debug_event($message = '', $internal = false)
    {
        return self::report_event(0, $message, $internal);
    }

    /**
     * Reports a notice event on the website.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_notice_event($message = '', $internal = false)
    {
        return self::report_event(1, $message, $internal);
    }

    /**
     * Reports a info event on the website.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_info_event($message = '', $internal = false)
    {
        return self::report_event(2, $message, $internal);
    }

    /**
     * Reports a warning event on the website.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_warning_event($message = '', $internal = false)
    {
        return self::report_event(3, $message, $internal);
    }

    /**
     * Reports a error event on the website.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_error_event($message = '', $internal = false)
    {
        return self::report_event(4, $message, $internal);
    }

    /**
     * Reports a critical event on the website.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_critical_event($message = '', $internal = false)
    {
        return self::report_event(5, $message, $internal);
    }

    /**
     * Reports a notice or error event for enable and disable actions.
     *
     * @param  string  $message  Text witht the explanation of the event or action performed.
     * @param  string  $action   An optional text, hopefully either enabled or disabled.
     * @param  boolean $internal Whether the event will be publicly visible or not.
     * @return boolean           Either true or false depending on the success of the operation.
     */
    public static function report_auto_event($message = '', $action = '', $internal = false)
    {
        $message = strip_tags($message);

        // Auto-detect the action performed, either enabled or disabled.
        if (preg_match('/( was )?(enabled|disabled)$/', $message, $match)) {
            $action = $match[2];
        }

        // Report the correct event for the action performed.
        if ($action == 'enabled') {
            return self::report_notice_event($message, $internal);
        } elseif ($action == 'disabled') {
            return self::report_error_event($message, $internal);
        } else {
            return self::report_info_event($message, $internal);
        }
    }

    /**
     * Reports an esception on the code.
     *
     * @param  Exception $exception A valid exception object of any type.
     * @return boolean              Whether the report was filled correctly or not.
     */
    public static function report_exception($exception = false)
    {
        if ($exception) {
            $e_trace = $exception->getTrace();
            $multiple_entries = array();

            foreach ($e_trace as $e_child) {
                $e_file = array_key_exists('file', $e_child)
                    ? basename($e_child['file'])
                    : '[internal function]';
                $e_line = array_key_exists('line', $e_child)
                    ? basename($e_child['line'])
                    : '0';
                $e_function = array_key_exists('class', $e_child)
                    ? $e_child['class'] . $e_child['type'] . $e_child['function']
                    : $e_child['function'];
                $multiple_entries[] = sprintf(
                    '%s(%s): %s',
                    $e_file,
                    $e_line,
                    $e_function
                );
            }

            $report_message = sprintf(
                '%s: (multiple entries): %s',
                $exception->getMessage(),
                @implode(',', $multiple_entries)
            );

            return self::report_debug_event($report_message);
        }

        return false;
    }

    /**
     * Send a notification to the administrator of the specified events, only if
     * the administrator accepted to receive alerts for this type of events.
     *
     * @param  string $event   The name of the event that was triggered.
     * @param  string $content Body of the email that will be sent to the administrator.
     * @return void
     */
    public static function notify_event($event = '', $content = '')
    {
        $notify = GddysecOption::get_option(':notify_' . $event);
        $email = GddysecOption::get_option(':notify_to');
        $email_params = array();

        if ($notify == 'enabled') {
            if ($event == 'post_publication') {
                $event = 'post_update';
            } elseif ($event == 'failed_login') {
                $settings_url = GddysecTemplate::getUrl('settings');
                $content .= "<br>\n<br>\n<em>Explanation: Someone failed to login to your "
                    . "site. If you are getting too many of these messages, it is likely your "
                    . "site is under a password guessing brute-force attack [1]. You can disable "
                    . "the failed login alerts from here [2]. Alternatively, you can consider "
                    . "to install a firewall between your website and your visitors to filter "
                    . "out these and other attacks, take a look at Sucuri CloudProxy [3].</em>"
                    . "<br>\n<br>\n"
                    . "[1] <a href='https://kb.sucuri.net/definitions/attacks/brute-force/password-guessing'>"
                    . "https://kb.sucuri.net/definitions/attacks/brute-force/password-guessing</a><br>\n"
                    . "[2] <a href='" . $settings_url . "'>" . $settings_url . "</a> <br>\n"
                    . "[3] <a href='https://sucuri.net/website-firewall/?wpalert'>"
                    . "https://sucuri.net/website-firewall/</a> <br>\n";
            } elseif ($event == 'bruteforce_attack') {
                // Send a notification even if the limit of emails per hour was reached.
                $email_params['Force'] = true;
            } elseif ($event == 'scan_checksums') {
                $event = 'core_integrity_checks';
                $email_params['Force'] = true;
                $email_params['ForceHTML'] = true;
            } elseif ($event == 'available_updates') {
                $email_params['Force'] = true;
                $email_params['ForceHTML'] = true;
            }

            $title = str_replace('_', "\x20", $event);
            $mail_sent = GddysecMail::send_mail(
                $email,
                $title,
                $content,
                $email_params
            );

            return $mail_sent;
        }

        return false;
    }
}
