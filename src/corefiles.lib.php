<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Checks the integrity of the WordPress installation.
 *
 * This tool finds changes in the standard WordPress installation. Files located
 * in the root directory, wp-admin and wp-includes will be compared against the
 * files distributed with the current WordPress version; all files with
 * inconsistencies will be listed here.
 */
class GddysecCoreFiles
{
    /**
     * Compare the md5sum of the core files in the current site with the hashes hosted
     * remotely in Sucuri servers. These hashes are updated every time a new version
     * of WordPress is released. If the "Send Email" parameter is set the function will
     * send a notification to the administrator with a list of files that were added,
     * modified and/or deleted so far.
     *
     * @return string HTML code with a list of files that were affected.
     */
    public static function pageCoreFiles()
    {
        $params = array();

        self::pageCoreFilesSubmission();

        return GddysecTemplate::getSection('corefiles', $params);
    }

    public static function ajaxCoreFiles()
    {
        if (GddysecRequest::post('form_action') !== 'get_core_files') {
            return;
        }

        $response = GddysecCoreFiles::getCoreFilesStatus();
        print($response);
        exit(0);
    }

    /**
     * Process the requests sent by the form submissions originated in the integrity
     * page, all forms must have a nonce field that will be checked against the one
     * generated in the template render function.
     *
     * @return void
     */
    private static function pageCoreFilesSubmission()
    {
        if (!GddysecInterface::check_nonce()) {
            return;
        }

        // Restore, Remove, Mark as fixed the core files.
        $action = GddysecRequest::post(':integrity_action');

        // Skip if an invalid action was sent.
        if ($action === false) {
            return;
        }

        // Skip if the user didn't confirm the operation.
        if (GddysecRequest::post(':process_form') != 1) {
            GddysecInterface::error('You need to confirm that you understand the risk of this operation.');
            return;
        }

        // Skip if the requested action is not currently supported.
        if ($action !== 'fixed' && $action !== 'delete' && $action !== 'restore') {
            GddysecInterface::error('Action requested is not supported.');
            return;
        }

        // Process the HTTP request.
        $cache = new GddysecCache('integrity');
        $core_files = GddysecRequest::post(':corefiles', '_array');
        $files_selected = count($core_files);
        $files_affected = array();
        $files_processed = 0;
        $action_titles = array(
            'restore' => 'Core file restored',
            'delete' => 'Non-core file deleted',
            'fixed' => 'Core file marked as fixed',
        );

        // Skip if no files were selected.
        if (!$core_files) {
            GddysecInterface::error('No files were selected.');
            return;
        }

        $delimiter = '@';
        $parts_count = 2;

        foreach ($core_files as $file_meta) {
            if (strpos($file_meta, $delimiter)) {
                $parts = explode($delimiter, $file_meta, $parts_count);

                if (count($parts) === $parts_count) {
                    $file_path = $parts[1];
                    $status_type = $parts[0];

                    // Do not use realpath as the file may not exists.
                    $full_path = ABSPATH . '/' . $file_path;

                    switch ($action) {
                        case 'restore':
                            $file_content = GddysecAPI::getOriginalCoreFile($file_path);
                            if ($file_content) {
                                $basedir = dirname($full_path);
                                if (!file_exists($basedir)) {
                                    @mkdir($basedir, 0755, true);
                                }
                                if (file_exists($basedir)) {
                                    $restored = @file_put_contents($full_path, $file_content);
                                    $files_processed += ($restored ? 1 : 0);
                                    $files_affected[] = $full_path;
                                }
                            }
                            break;
                        case 'fixed':
                            $cache_key = md5($file_path);
                            $cache_value = array(
                                'file_path' => $file_path,
                                'file_status' => $status_type,
                                'ignored_at' => time(),
                            );
                            $cached = $cache->add($cache_key, $cache_value);
                            $files_processed += ($cached ? 1 : 0);
                            $files_affected[] = $full_path;
                            break;
                        case 'delete':
                            if (@unlink($full_path)) {
                                $files_processed += 1;
                                $files_affected[] = $full_path;
                            }
                            break;
                    }
                }
            }
        }

        // Report files affected as a single event.
        if (!empty($files_affected)) {
            $message_tpl = (count($files_affected) > 1)
                ? '%s: (multiple entries): %s'
                : '%s: %s';
            $message = sprintf(
                $message_tpl,
                $action_titles[$action],
                @implode(',', $files_affected)
            );

            switch ($action) {
                case 'restore':
                    GddysecEvent::report_info_event($message);
                    break;
                case 'delete':
                    GddysecEvent::report_notice_event($message);
                    break;
                case 'fixed':
                    GddysecEvent::report_warning_event($message);
                    break;
            }
        }

        GddysecInterface::info(sprintf(
            '<b>%d</b> out of <b>%d</b> files were successfully processed.',
            $files_processed,
            $files_selected
        ));
    }

    public static function getCoreFilesStatus($send_email = false)
    {
        $params = array();
        $affected_files = 0;
        $site_version = Gddysec::site_version();
        $integrity_is_enabled = GddysecOption::is_enabled(':scan_checksums');

        $params['Version'] = Gddysec::site_version();
        $params['CoreFiles.List'] = '';
        $params['CoreFiles.ListCount'] = 0;
        $params['CoreFiles.RemoteChecksumsURL'] = '';
        $params['CoreFiles.BadVisibility'] = 'hidden';
        $params['CoreFiles.GoodVisibility'] = 'visible';
        $params['CoreFiles.FailureVisibility'] = 'hidden';
        $params['CoreFiles.NotFixableVisibility'] = 'hidden';
        $params['CoreFiles.DisabledVisibility'] = 'hidden';

        if ($integrity_is_enabled !== true) {
            $params['CoreFiles.GoodVisibility'] = 'hidden';
            $params['CoreFiles.DisabledVisibility'] = 'visible';
        }

        if ($site_version && $integrity_is_enabled) {
            // Check if there are added, removed, or modified files.
            $latest_hashes = self::checkCoreFilesIntegrity($site_version);
            $language = GddysecOption::get_option(':language');
            $params['CoreFiles.RemoteChecksumsURL'] =
                'https://api.wordpress.org/core/checksums/1.0/'
                . '?version=' . $site_version . '&locale=' . $language;

            if ($latest_hashes) {
                $cache = new GddysecCache('integrity');
                $ignored_files = $cache->getAll();
                $counter = 0;

                foreach ($latest_hashes as $list_type => $file_list) {
                    if ($list_type == 'stable' || empty($file_list)) {
                        continue;
                    }

                    foreach ($file_list as $file_info) {
                        $file_path = $file_info['filepath'];
                        $full_filepath = sprintf('%s/%s', rtrim(ABSPATH, '/'), $file_path);

                        // Skip files that were marked as fixed.
                        if ($ignored_files) {
                            // Get the checksum of the base file name.
                            $file_path_checksum = md5($file_path);

                            if (array_key_exists($file_path_checksum, $ignored_files)) {
                                continue;
                            }
                        }

                        // Add extra information to the file list.
                        $css_class = ( $counter % 2 == 0 ) ? '' : 'alternate';
                        $file_size = @filesize($full_filepath);
                        $is_fixable_text = '';

                        // Check whether the file can be fixed automatically or not.
                        if ($file_info['is_fixable'] !== true) {
                            $css_class .= ' gddysec-opacity';
                            $is_fixable_text = '(not fixable)';
                            $params['CoreFiles.NotFixableVisibility'] = 'visible';
                        }

                        // Generate the HTML code from the snippet template for this file.
                        $params['CoreFiles.List'] .= GddysecTemplate::getSnippet(
                            'corefiles',
                            array(
                                'CoreFiles.CssClass' => $css_class,
                                'CoreFiles.StatusType' => $list_type,
                                'CoreFiles.FilePath' => $file_path,
                                'CoreFiles.FileSize' => $file_size,
                                'CoreFiles.FileSizeHuman' => Gddysec::human_filesize($file_size),
                                'CoreFiles.FileSizeNumber' => number_format($file_size),
                                'CoreFiles.ModifiedAt' => Gddysec::datetime($file_info['modified_at']),
                                'CoreFiles.IsNotFixable' => $is_fixable_text,
                            )
                        );
                        $counter += 1;
                        $affected_files += 1;
                    }
                }

                if ($counter > 0) {
                    $params['CoreFiles.ListCount'] = $counter;
                    $params['CoreFiles.GoodVisibility'] = 'hidden';
                    $params['CoreFiles.BadVisibility'] = 'visible';
                }
            } else {
                $params['CoreFiles.GoodVisibility'] = 'hidden';
                $params['CoreFiles.BadVisibility'] = 'hidden';
                $params['CoreFiles.FailureVisibility'] = 'visible';
            }
        }

        // Send an email notification with the affected files.
        if ($send_email === true) {
            if ($affected_files > 0) {
                $content = GddysecTemplate::getSection('corefiles-notification', $params);
                $sent = GddysecEvent::notify_event('scan_checksums', $content);

                return $sent;
            }

            return false;
        }

        if ($affected_files === 0) {
            return GddysecTemplate::getSection('corefiles-correct', $params);
        }

        return GddysecTemplate::getSection('corefiles-incorrect', $params);
    }

    /**
     * Retrieve a list of md5sum and last modification time of all the files in the
     * folder specified. This is a recursive function.
     *
     * @param  string  $dir       The base path where the scanning will start.
     * @param  boolean $recursive Either TRUE or FALSE if the scan should be performed recursively.
     * @return array              List of arrays containing the md5sum and last modification time of the files found.
     */
    public static function coreFilesTree($dir = './', $recursive = false)
    {
        $file_info = new GddysecFileInfo();
        $file_info->ignore_files = false;
        $file_info->ignore_directories = false;
        $file_info->run_recursively = $recursive;
        $tree = $file_info->get_directory_tree_md5($dir, true);

        if (!$tree) {
            $tree = array();
        }

        return $tree;
    }

    /**
     * Check whether the core WordPress files where modified, removed or if any file
     * was added to the core folders. This function returns an associative array with
     * these keys:
     *
     * <ul>
     *   <li>modified: Files with a different checksum according to the official WordPress archives,</li>
     *   <li>stable: Files with the same checksums than the official files,</li>
     *   <li>removed: Official files which are not present in the local project,</li>
     *   <li>added: Files present in the local project but not in the official WordPress packages.</li>
     * </ul>
     *
     * @param  integer $version Valid version number of the WordPress project.
     * @return array            Associative array with these keys: modified, stable, removed, added.
     */
    private static function checkCoreFilesIntegrity($version = 0)
    {
        $latest_hashes = GddysecAPI::getOfficialChecksums($version);
        $base_content_dir = defined('WP_CONTENT_DIR')
            ? basename(rtrim(WP_CONTENT_DIR, '/'))
            : '';

        if (!$latest_hashes) {
            return false;
        }

        $output = array(
            'added' => array(),
            'removed' => array(),
            'modified' => array(),
            'stable' => array(),
        );

        // Get current filesystem tree.
        $wp_top_hashes = self::coreFilesTree(ABSPATH, false);
        $wp_admin_hashes = self::coreFilesTree(ABSPATH . 'wp-admin', true);
        $wp_includes_hashes = self::coreFilesTree(ABSPATH . 'wp-includes', true);
        $wp_core_hashes = array_merge($wp_top_hashes, $wp_admin_hashes, $wp_includes_hashes);

        // Compare remote and local checksums and search removed files.
        foreach ($latest_hashes as $file_path => $remote) {
            if (self::ignoreThisFile($file_path)) {
                continue;
            }

            $full_filepath = sprintf('%s/%s', ABSPATH, $file_path);

            // Patch for custom content directory path.
            if (!file_exists($full_filepath)
                && strpos($file_path, 'wp-content') !== false
                && defined('WP_CONTENT_DIR')
            ) {
                $file_path = str_replace('wp-content', $base_content_dir, $file_path);
                $dir_content_dir = dirname(rtrim(WP_CONTENT_DIR, '/'));
                $full_filepath = sprintf('%s/%s', $dir_content_dir, $file_path);
            }

            // Check whether the official file exists or not.
            if (file_exists($full_filepath)) {
                $local = @md5_file($full_filepath);

                if ($local !== false && $local === $remote) {
                    $output['stable'][] = array(
                        'filepath' => $file_path,
                        'is_fixable' => false,
                        'modified_at' => 0,
                    );
                } else {
                    $modified_at = @filemtime($full_filepath);
                    $is_fixable = (bool) is_writable($full_filepath);
                    $output['modified'][] = array(
                        'filepath' => $file_path,
                        'is_fixable' => $is_fixable,
                        'modified_at' => $modified_at,
                    );
                }
            } else {
                $is_fixable = is_writable(dirname($full_filepath));
                $output['removed'][] = array(
                    'filepath' => $file_path,
                    'is_fixable' => $is_fixable,
                    'modified_at' => 0,
                );
            }
        }

        // Search added files (files not common in a normal wordpress installation).
        foreach ($wp_core_hashes as $file_path => $extra_info) {
            $file_path = str_replace(DIRECTORY_SEPARATOR, '/', $file_path);
            $file_path = @preg_replace('/^\.\/(.*)/', '$1', $file_path);

            if (self::ignoreThisFile($file_path)) {
                continue;
            }

            if (!array_key_exists($file_path, $latest_hashes)) {
                $full_filepath = ABSPATH . '/' . $file_path;
                $modified_at = @filemtime($full_filepath);
                $is_fixable = (bool) is_writable($full_filepath);
                $output['added'][] = array(
                    'filepath' => $file_path,
                    'is_fixable' => $is_fixable,
                    'modified_at' => $modified_at,
                );
            }
        }

        return $output;
    }

    /**
     * Ignore irrelevant files and directories from the integrity checking.
     *
     * @param  string  $file_path File path that will be compared.
     * @return boolean            TRUE if the file should be ignored, FALSE otherwise.
     */
    private static function ignoreThisFile($file_path = '')
    {
        global $wp_local_package;

        // List of files that will be ignored from the integrity checking.
        $ignore_files = array(
            '^sucuri-[0-9a-z\-]+\.php$',
            '^\S+-sucuri-db-dump-gzip-[0-9]{10}-[0-9a-z]{32}\.gz$',
            '\.ico$',
            '^php\.ini$',
            '^\.(htaccess|htpasswd|ftpquota)$',
            '^wp-includes\/\.htaccess$',
            '^wp-admin\/setup-config\.php$',
            '^wp-(config|pass|rss|feed|register|atom|commentsrss2|rss2|rdf)\.php$',
            '^wp-content\/(themes|plugins)\/.+', // TODO: Add the popular themes/plugins integrity checks.
            '^sitemap\.xml($|\.gz)$',
            '^readme(\.[a-z0-9]{32})?\.html$',
            '^(503|404)\.php$',
            '^500\.(shtml|php)$',
            '^40[0-9]\.shtml$',
            '^([^\/]*)\.(pdf|css|txt|jpg|gif|png|jpeg)$',
            '^google[0-9a-z]{16}\.html$',
            '^pinterest-[0-9a-z]{5}\.html$',
            '(^|\/)error_log$',
        );

        /**
         * Ignore i18n files.
         *
         * Sites with i18n have differences compared with the official English version
         * of the project, basically they have files with new variables specifying the
         * language that will be used in the admin panel, site options, and emails.
         */
        if (isset($wp_local_package) && $wp_local_package != 'en_US') {
            $ignore_files[] = 'wp-includes\/version\.php';
            $ignore_files[] = 'wp-config-sample\.php';
        }

        // Determine whether a file must be ignored from the integrity checks or not.
        foreach ($ignore_files as $ignore_pattern) {
            if (@preg_match('/'.$ignore_pattern.'/', $file_path)) {
                return true;
            }
        }

        return false;
    }
}
