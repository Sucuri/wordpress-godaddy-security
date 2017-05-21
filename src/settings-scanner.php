<?php

/**
 * Code related to the settings-scanner.php interface.
 *
 * PHP version 5
 *
 * @category   Library
 * @package    GoDaddy
 * @subpackage GoDaddySecurity
 * @author     Daniel Cid <dcid@sucuri.net>
 * @copyright  2017 Sucuri Inc. - GoDaddy LLC.
 * @license    https://www.godaddy.com/ - Proprietary
 * @link       https://wordpress.org/plugins/godaddy-security
 */

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Returns the HTML to configure the scanner.
 *
 * @category   Library
 * @package    GoDaddy
 * @subpackage GoDaddySecurity
 * @author     Daniel Cid <dcid@sucuri.net>
 * @copyright  2017 Sucuri Inc. - GoDaddy LLC.
 * @license    https://www.godaddy.com/ - Proprietary
 * @link       https://wordpress.org/plugins/godaddy-security
 */
class GddysecSettingsScanner extends GddysecSettings
{
    /**
     * Renders a page with information about the cronjobs feature.
     *
     * @param  bool $nonce True if the CSRF protection worked.
     * @return string      Page with information about the cronjobs.
     */
    public static function cronjobs($nonce)
    {
        $params = array(
            'Cronjobs.List' => '',
            'Cronjobs.Total' => 0,
            'Cronjob.Schedules' => '',
        );

        if ($nonce) {
            // Modify the scheduled tasks (run now, remove, re-schedule).
            $allowed_actions = array_keys(GddysecEvent::availableSchedules());
            $allowed_actions[] = 'runnow'; /* execute in the next 10 seconds */
            $allowed_actions[] = 'remove'; /* can be reinstalled automatically */
            $allowed_actions = sprintf('(%s)', implode('|', $allowed_actions));
            $cronjob_action = GddysecRequest::post(':cronjob_action', $allowed_actions);

            if ($cronjob_action) {
                $cronjobs = GddysecRequest::post(':cronjobs', '_array');

                if (!empty($cronjobs)) {
                    $total_tasks = count($cronjobs);

                    if ($cronjob_action == 'runnow') {
                        /* Force execution of the selected scheduled tasks. */
                        GddysecInterface::info(
                            sprintf(
                                '%d tasks has been scheduled to run in the next ten seconds.',
                                $total_tasks /* some cronjobs will be ignored */
                            )
                        );
                        GddysecEvent::reportNoticeEvent(
                            sprintf(
                                'Force execution of scheduled tasks: (multiple entries): %s',
                                @implode(',', $cronjobs)
                            )
                        );

                        foreach ($cronjobs as $task_name) {
                            wp_schedule_single_event(time() + 10, $task_name);
                        }
                    } elseif ($cronjob_action == 'remove' || $cronjob_action == '_oneoff') {
                        /* Force deletion of the selected scheduled tasks. */
                        GddysecInterface::info(
                            sprintf(
                                '%d scheduled tasks have been removed.',
                                $total_tasks /* some cronjobs will be ignored */
                            )
                        );
                        GddysecEvent::reportNoticeEvent(
                            sprintf(
                                'Delete scheduled tasks: (multiple entries): %s',
                                @implode(',', $cronjobs)
                            )
                        );

                        foreach ($cronjobs as $task_name) {
                            wp_clear_scheduled_hook($task_name);
                        }
                    } else {
                        GddysecInterface::info(
                            sprintf(
                                '%d tasks has been re-scheduled to run <code>%s</code>.',
                                $total_tasks, /* some cronjobs will be ignored */
                                $cronjob_action /* frequency to run cronjob */
                            )
                        );
                        GddysecEvent::reportNoticeEvent(
                            sprintf(
                                'Re-configure scheduled tasks %s: (multiple entries): %s',
                                $cronjob_action,
                                @implode(',', $cronjobs)
                            )
                        );

                        foreach ($cronjobs as $task_name) {
                            $next_due = wp_next_scheduled($task_name);
                            wp_schedule_event($next_due, $cronjob_action, $task_name);
                        }
                    }
                } else {
                    GddysecInterface::error('No scheduled tasks were selected from the list.');
                }
            }
        }

        $cronjobs = _get_cron_array();
        $available = GddysecEvent::availableSchedules();

        /* Hardcode the first one to allow the immediate execution of the cronjob(s) */
        $params['Cronjob.Schedules'] .= '<option value="runnow">'
        . 'Execute Now (in +10 seconds)' . '</option>';

        foreach ($available as $freq => $name) {
            $params['Cronjob.Schedules'] .= sprintf('<option value="%s">%s</option>', $freq, $name);
        }

        foreach ($cronjobs as $timestamp => $cronhooks) {
            foreach ((array) $cronhooks as $hook => $events) {
                foreach ((array) $events as $key => $event) {
                    if (empty($event['args'])) {
                        $event['args'] = array('[]');
                    }

                    $params['Cronjobs.Total'] += 1;
                    $params['Cronjobs.List'] .= GddysecTemplate::getSnippet(
                        'settings-scanner-cronjobs',
                        array(
                            'Cronjob.Hook' => $hook,
                            'Cronjob.Schedule' => $event['schedule'],
                            'Cronjob.NextTime' => Gddysec::datetime($timestamp),
                            'Cronjob.NextTimeHuman' => Gddysec::humanTime($timestamp),
                            'Cronjob.Arguments' => Gddysec::implode(', ', $event['args']),
                        )
                    );
                }
            }
        }

        $hasSPL = GddysecFileInfo::isSplAvailable();
        $params['NoSPL.Visibility'] = GddysecTemplate::visibility(!$hasSPL);

        return GddysecTemplate::getSection('settings-scanner-cronjobs', $params);
    }

    /**
     * Returns a list of directories in the website.
     *
     * @return void
     */
    public static function ignoreFoldersAjax()
    {
        if (GddysecRequest::post('form_action') !== 'get_ignored_files') {
            return;
        }

        $response = ''; /* request response */
        $ignored_dirs = GddysecFSScanner::getIgnoredDirectoriesLive();

        foreach ($ignored_dirs as $group => $dir_list) {
            foreach ($dir_list as $dir_data) {
                $valid_entry = false;
                $snippet = array(
                    'IgnoreScan.Directory' => '',
                    'IgnoreScan.DirectoryPath' => '',
                    'IgnoreScan.IgnoredAt' => '',
                    'IgnoreScan.IgnoredAtText' => 'OK',
                );

                if ($group == 'is_ignored') {
                    $valid_entry = true;
                    $snippet['IgnoreScan.Directory'] = urlencode($dir_data['directory_path']);
                    $snippet['IgnoreScan.DirectoryPath'] = $dir_data['directory_path'];
                    $snippet['IgnoreScan.IgnoredAt'] = Gddysec::datetime($dir_data['ignored_at']);
                    $snippet['IgnoreScan.IgnoredAtText'] = 'Ignored';
                } elseif ($group == 'is_not_ignored') {
                    $valid_entry = true;
                    $snippet['IgnoreScan.Directory'] = urlencode($dir_data);
                    $snippet['IgnoreScan.DirectoryPath'] = $dir_data;
                }

                if ($valid_entry) {
                    $response .= GddysecTemplate::getSnippet('settings-scanner-ignore-folders', $snippet);
                }
            }
        }

        wp_send_json($response, true);
    }

    /**
     * Returns the HTML for the folder scanner skipper.
     *
     * If the website has too many files it would be wise to force the plugin to
     * ignore some directories that are not relevant for the scanner. This includes
     * directories with media files like images, audio, videos, etc and directories
     * used to store cache data.
     *
     * @param  bool $nonce True if the CSRF protection worked, false otherwise.
     * @return string      HTML for the folder scanner skipper.
     */
    public static function ignoreFolders($nonce)
    {
        $params = array();

        if ($nonce) {
            // Ignore a new directory path for the file system scans.
            $ign_file = GddysecRequest::post(':ignorescanning_file');
            $ign_dirs = GddysecRequest::post(':ignorescanning_dirs', '_array');

            if (GddysecRequest::post(':ignorescanning_action') === 'ignore') {
                // Target a single file path to be ignored.
                if ($ign_file !== false) {
                    $ign_dirs = array($ign_file);
                    unset($_POST['gddysec_ignorescanning_file']);
                }

                // Target a list of directories to be ignored.
                if (is_array($ign_dirs) && !empty($ign_dirs)) {
                    $were_ignored = 0;

                    foreach ($ign_dirs as $resource_path) {
                        if (file_exists($resource_path)
                            && GddysecFSScanner::ignoreDirectory($resource_path)
                        ) {
                            $were_ignored++;
                        }
                    }

                    if ($were_ignored > 0) {
                        GddysecInterface::info('Selected files have been successfully processed.');
                        GddysecEvent::reportWarningEvent(
                            sprintf(
                                'Resources will not be scanned: (multiple entries): %s',
                                @implode(',', $ign_dirs)
                            )
                        );
                    }
                }
            }

            if (GddysecRequest::post(':ignorescanning_action') === 'unignore') {
                if (is_array($ign_dirs) && !empty($ign_dirs)) {
                    $were_ignored = 0;

                    foreach ($ign_dirs as $directory_path) {
                        GddysecFSScanner::unignoreDirectory($directory_path);
                        $were_ignored++;
                    }

                    if ($were_ignored > 0) {
                        GddysecInterface::info('Selected files have been successfully processed.');
                        GddysecEvent::reportNoticeEvent(
                            sprintf(
                                'Resources will be scanned: (multiple entries): %s',
                                @implode(',', $ign_dirs)
                            )
                        );
                    }
                }
            }
        }

        return GddysecTemplate::getSection('settings-scanner-ignore-folders', $params);
    }
}
