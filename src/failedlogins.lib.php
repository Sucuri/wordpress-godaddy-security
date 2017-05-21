<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

class GddysecFailedLogins extends GddysecEvent
{
    /**
     * Find the full path of the file where the information of the failed logins
     * will be stored, it will be created automatically if does not exists (and if
     * the destination folder has permissions to write). This function can also be
     * used to reset the content of the datastore file.
     *
     * @param  boolean $reset Whether the file will be resetted or not.
     * @return string         The full (relative) path where the file is located.
     */
    private static function datastorePath($reset = false)
    {
        $file_name = 'gddysec-failedlogins.php';
        $datastore_path = Gddysec::datastore_folder_path($file_name);
        $default_content = self::defaultContent();

        // Create the file if it does not exists.
        if (!file_exists($datastore_path) || $reset) {
            @file_put_contents($datastore_path, $default_content, LOCK_EX);
        }

        // Return the datastore path if the file exists (or was created).
        if (file_exists($datastore_path) && is_readable($datastore_path)) {
            return $datastore_path;
        }

        return false;
    }

    /**
     * Default content of the datastore file where the failed logins are being kept.
     *
     * @return string Default content of the file.
     */
    private static function defaultContent()
    {
        return "<?php exit(0); ?>\n";
    }

    /**
     * Read and parse the content of the datastore file where the failed logins are
     * being kept. This function will also calculate the difference in time between
     * the first and last login attempt registered in the file to later decide if
     * there is a brute-force attack in progress (and send an email notification
     * with the report) or reset the file after considering it a normal behavior of
     * the site.
     *
     * @return array Information and entries gathered from the failed logins datastore file.
     */
    public static function getData()
    {
        $datastore_path = self::datastorePath();

        if ($datastore_path) {
            $lines = GddysecFileInfo::file_lines($datastore_path);

            if ($lines) {
                $failed_logins = array(
                    'count' => 0,
                    'first_attempt' => 0,
                    'last_attempt' => 0,
                    'diff_time' => 0,
                    'entries' => array(),
                );

                // Read and parse all the entries found in the datastore file.
                $offset = count($lines) - 1;

                for ($key = $offset; $key >= 0; $key--) {
                    $line = trim($lines[ $key ]);
                    $login_data = @json_decode($line, true);

                    if (is_array($login_data)) {
                        $login_data['attempt_date'] = date('r', $login_data['attempt_time']);
                        $login_data['attempt_count'] = ( $key + 1 );

                        if (!$login_data['user_agent']) {
                            $login_data['user_agent'] = 'Unknown';
                        }

                        if (!isset($login_data['user_password'])) {
                            $login_data['user_password'] = '';
                        }

                        $failed_logins['entries'][] = $login_data;
                        $failed_logins['count'] += 1;
                    }
                }

                // Calculate the different time between the first and last attempt.
                if ($failed_logins['count'] > 0) {
                    $z = abs($failed_logins['count'] - 1);
                    $failed_logins['last_attempt'] = $failed_logins['entries'][ $z ]['attempt_time'];
                    $failed_logins['first_attempt'] = $failed_logins['entries'][0]['attempt_time'];
                    $failed_logins['diff_time'] = abs($failed_logins['last_attempt'] - $failed_logins['first_attempt']);

                    return $failed_logins;
                }
            }
        }

        return false;
    }

    /**
     * Add a new entry in the datastore file where the failed logins are being kept,
     * this entry will contain the username, timestamp of the login attempt, remote
     * address of the computer sending the request, and the user-agent.
     *
     * @param  string  $user_login     Information from the current failed login event.
     * @param  string  $wrong_password Wrong password used during the supposed attack.
     * @return boolean                 Whether the information of the current failed login event was stored or not.
     */
    public static function log($user_login = '', $wrong_password = '')
    {
        $datastore_path = self::datastorePath();

        if ($datastore_path) {
            $login_data = json_encode(array(
                'user_login' => $user_login,
                'user_password' => $wrong_password,
                'attempt_time' => time(),
                'remote_addr' => Gddysec::remoteAddr(),
                'user_agent' => Gddysec::userAgent(),
            ));

            $written = @file_put_contents(
                $datastore_path,
                $login_data . "\n",
                FILE_APPEND
            );

            if ($written > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Read and parse all the entries in the datastore file where the failed logins
     * are being kept, this will loop through all these items and generate a table
     * in HTML code to send as a report via email according to the plugin settings
     * for the email notifications.
     *
     * @param  array   $failed_logins Information and entries gathered from the failed logins datastore file.
     * @return boolean                Whether the report was sent via email or not.
     */
    public static function report($failed_logins = array())
    {
        if ($failed_logins && $failed_logins['count'] > 0) {
            $prettify_mails = GddysecMail::prettify_mails();
            $mail_content = '';

            if ($prettify_mails) {
                $table_html  = '<table border="1" cellspacing="0" cellpadding="0">';

                // Add the table headers.
                $table_html .= '<thead>';
                $table_html .= '<tr>';
                $table_html .= '<th>Username</th>';
                $table_html .= '<th>Password</th>';
                $table_html .= '<th>IP Address</th>';
                $table_html .= '<th>Attempt Timestamp</th>';
                $table_html .= '<th>Attempt Date/Time</th>';
                $table_html .= '</tr>';
                $table_html .= '</thead>';

                $table_html .= '<tbody>';
            }

            foreach ($failed_logins['entries'] as $login_data) {
                if ($prettify_mails) {
                    $table_html .= '<tr>';
                    $table_html .= '<td>' . esc_attr($login_data['user_login']) . '</td>';
                    $table_html .= '<td>' . esc_attr($login_data['user_password']) . '</td>';
                    $table_html .= '<td>' . esc_attr($login_data['remote_addr']) . '</td>';
                    $table_html .= '<td>' . $login_data['attempt_time'] . '</td>';
                    $table_html .= '<td>' . $login_data['attempt_date'] . '</td>';
                    $table_html .= '</tr>';
                } else {
                    $mail_content .= "\n";
                    $mail_content .= 'Username: ' . $login_data['user_login'] . "\n";
                    $mail_content .= 'Password: ' . $login_data['user_password'] . "\n";
                    $mail_content .= 'IP Address: ' . $login_data['remote_addr'] . "\n";
                    $mail_content .= 'Attempt Timestamp: ' . $login_data['attempt_time'] . "\n";
                    $mail_content .= 'Attempt Date/Time: ' . $login_data['attempt_date'] . "\n";
                }
            }

            if ($prettify_mails) {
                $table_html .= '</tbody>';
                $table_html .= '</table>';
                $mail_content = $table_html;
            }

            if (GddysecEvent::notify_event('bruteforce_attack', $mail_content)) {
                self::reset();

                return true;
            }
        }

        return false;
    }

    /**
     * Remove all the entries in the datastore file where the failed logins are
     * being kept. The execution of this function will not delete the file (which is
     * likely the best move) but rather will clean its content and append the
     * default code defined by another function above.
     *
     * @return boolean Whether the datastore file was resetted or not.
     */
    public static function reset()
    {
        $datastore_path = Gddysec::datastore_folder_path('gddysec-failedlogins.php');
        $datastore_backup_path = self::datastorePath(false);
        $default_content = self::defaultContent();
        $current_content = @file_get_contents($datastore_path);
        $current_content = str_replace($default_content, '', $current_content);

        @file_put_contents(
            $datastore_backup_path,
            $current_content,
            FILE_APPEND
        );

        return (bool) self::datastorePath(true);
    }
}
