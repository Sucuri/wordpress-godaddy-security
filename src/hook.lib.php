<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Function call interceptors.
 *
 * The term hooking covers a range of techniques used to alter or augment the
 * behavior of an operating system, of applications, or of other software
 * components by intercepting function calls or messages or events passed
 * between software components. Code that handles such intercepted function
 * calls, events or messages is called a "hook".
 *
 * Hooking is used for many purposes, including debugging and extending
 * functionality. Examples might include intercepting keyboard or mouse event
 * messages before they reach an application, or intercepting operating system
 * calls in order to monitor behavior or modify the function of an application
 * or other component; it is also widely used in benchmarking programs.
 */
class GddysecHook extends GddysecEvent
{
    /**
     * Send to Sucuri servers an alert notifying that an attachment was added to a post.
     *
     * @param  integer $id The post identifier.
     * @return void
     */
    public static function hook_add_attachment($id = 0)
    {
        if ($data = get_post($id)) {
            $id = $data->ID;
            $title = $data->post_title;
            $mime_type = $data->post_mime_type;
        } else {
            $title = 'unknown';
            $mime_type = 'unknown';
        }

        $message = sprintf('Media file added; identifier: %s; name: %s; type: %s', $id, $title, $mime_type);
        self::report_notice_event($message);
        self::notify_event('post_publication', $message);
    }

    /**
     * Send an alert notifying that a new link was added to the bookmarks.
     *
     * @param  integer $id Identifier of the new link created;
     * @return void
     */
    public static function hook_add_link($id = 0)
    {
        if ($data = get_bookmark($id)) {
            $id = $data->link_id;
            $title = $data->link_name;
            $url = $data->link_url;
            $target = $data->link_target;
        } else {
            $title = 'unknown';
            $url = 'undefined/url';
            $target = '_none';
        }

        $message = sprintf(
            'Bookmark link added; identifier: %s; name: %s; url: %s; target: %s',
            $id,
            $title,
            $url,
            $target
        );
        self::report_warning_event($message);
        self::notify_event('post_publication', $message);
    }

    /**
     * Send an alert notifying that a category was created.
     *
     * @param  integer $id The identifier of the category created.
     * @return void
     */
    public static function hook_create_category($id = 0)
    {
        $title = ( is_int($id) ? get_cat_name($id) : 'Unknown' );

        $message = sprintf('Category created; identifier: %s; name: %s', $id, $title);
        self::report_notice_event($message);
        self::notify_event('post_publication', $message);
    }

    /**
     * Send an alert notifying that a post was deleted.
     *
     * @param  integer $id The identifier of the post deleted.
     * @return void
     */
    public static function hook_delete_post($id = 0)
    {
        self::report_warning_event('Post deleted; identifier: ' . $id);
    }

    /**
     * Send an alert notifying that a post was moved to the trash.
     *
     * @param  integer $id The identifier of the trashed post.
     * @return void
     */
    public static function hook_wp_trash_post($id = 0)
    {
        if ($data = get_post($id)) {
            $title = $data->post_title;
            $status = $data->post_status;
        } else {
            $title = 'Unknown';
            $status = 'none';
        }

        $message = sprintf(
            'Post moved to trash; identifier: %s; name: %s; status: %s',
            $id,
            $title,
            $status
        );
        self::report_warning_event($message);
    }

    /**
     * Send an alert notifying that a user account was deleted.
     *
     * @param  integer $id The identifier of the user account deleted.
     * @return void
     */
    public static function hook_delete_user($id = 0)
    {
        self::report_warning_event('User account deleted; identifier: ' . $id);
    }

    /**
     * Send an alert notifying that an attempt to reset the password
     * of an user account was executed.
     *
     * @return void
     */
    public static function hook_login_form_resetpass()
    {
        // Detecting WordPress 2.8.3 vulnerability - $key is array.
        if (isset($_GET['key']) && is_array($_GET['key'])) {
            self::report_critical_event('Attempt to reset password by attacking WP/2.8.3 bug');
        }
    }

    /**
     * Send an alert notifying that the state of a post was changed
     * from private to published. This will only applies for posts not pages.
     *
     * @param  integer $id The identifier of the post changed.
     * @return void
     */
    public static function hook_private_to_published($id = 0)
    {
        if ($data = get_post($id)) {
            $title = $data->post_title;
            $p_type = ucwords($data->post_type);
        } else {
            $title = 'Unknown';
            $p_type = 'Publication';
        }

        // Check whether the post-type is being ignored to send notifications.
        if (!GddysecOption::isIgnoredEvent($p_type)) {
            $message = sprintf(
                '%s (private to published); identifier: %s; name: %s',
                $p_type,
                $id,
                $title
            );
            self::report_notice_event($message);
            self::notify_event('post_publication', $message);
        }
    }

    /**
     * Send an alert notifying that a post was published.
     *
     * @param  integer $id The identifier of the post or page published.
     * @return void
     */
    public static function hook_publish($id = 0)
    {
        if ($data = get_post($id)) {
            $title = $data->post_title;
            $p_type = ucwords($data->post_type);
            $action = ( $data->post_date == $data->post_modified ? 'created' : 'updated' );
        } else {
            $title = 'Unknown';
            $p_type = 'Publication';
            $action = 'published';
        }

        $message = sprintf(
            '%s was %s; identifier: %s; name: %s',
            $p_type,
            $action,
            $id,
            $title
        );
        self::report_notice_event($message);
        self::notify_event('post_publication', $message);
    }

    /**
     * Alias function for hook_publish()
     *
     * @param  integer $id The identifier of the post or page published.
     * @return void
     */
    public static function hook_publish_page($id = 0)
    {
        self::hook_publish($id);
    }

    /**
     * Alias function for hook_publish()
     *
     * @param  integer $id The identifier of the post or page published.
     * @return void
     */
    public static function hook_publish_post($id = 0)
    {
        self::hook_publish($id);
    }

    /**
     * Alias function for hook_publish()
     *
     * @param  integer $id The identifier of the post or page published.
     * @return void
     */
    public static function hook_publish_phone($id = 0)
    {
        self::hook_publish($id);
    }

    /**
     * Alias function for hook_publish()
     *
     * @param  integer $id The identifier of the post or page published.
     * @return void
     */
    public static function hook_xmlrpc_publish_post($id = 0)
    {
        self::hook_publish($id);
    }

    /**
     * Send an alert notifying that an attempt to retrieve the password
     * of an user account was tried.
     *
     * @param  string $title The name of the user account involved in the trasaction.
     * @return void
     */
    public static function hook_retrieve_password($title = '')
    {
        if (empty($title)) {
            $title = 'unknown';
        }

        self::report_error_event('Password retrieval attempt: ' . $title);
    }

    /**
     * Send an alert notifying that the theme of the site was changed.
     *
     * @param  string $title The name of the new theme selected to used through out the site.
     * @return void
     */
    public static function hook_switch_theme($title = '')
    {
        if (empty($title)) {
            $title = 'unknown';
        }

        $message = 'Theme activated: ' . $title;
        self::report_warning_event($message);
        self::notify_event('theme_activated', $message);
    }

    /**
     * Send an alert notifying that a new user account was created.
     *
     * @param  integer $id The identifier of the new user account created.
     * @return void
     */
    public static function hook_user_register($id = 0)
    {
        if ($data = get_userdata($id)) {
            $title = $data->user_login;
            $email = $data->user_email;
            $roles = @implode(', ', $data->roles);
        } else {
            $title = 'unknown';
            $email = 'user@domain.com';
            $roles = 'none';
        }

        $message = sprintf(
            'User account created; identifier: %s; name: %s; email: %s; roles: %s',
            $id,
            $title,
            $email,
            $roles
        );
        self::report_warning_event($message);
        self::notify_event('user_registration', $message);
    }

    /**
     * Send an alert notifying that an attempt to login into the
     * administration panel was successful.
     *
     * @param  string $title The name of the user account involved in the transaction.
     * @return void
     */
    public static function hook_wp_login($title = '')
    {
        if (empty($title)) {
            $title = 'Unknown';
        }

        $message = 'User authentication succeeded: ' . $title;
        self::report_notice_event($message);
        self::notify_event('success_login', $message);
    }

    /**
     * Send an alert notifying that an attempt to login into the
     * administration panel failed.
     *
     * @param  string $title The name of the user account involved in the transaction.
     * @return void
     */
    public static function hook_wp_login_failed($title = '')
    {
        if (empty($title)) {
            $title = 'Unknown';
        }

        $title = sanitize_user($title, true);
        $password = GddysecRequest::post('pwd');
        $message = 'User authentication failed: '
            . $title . "<br>\n"
            . "User wrong password: " . $password;

        self::report_error_event($message);
        self::notify_event('failed_login', $message);

        // Log the failed login in the internal datastore for future reports.
        $logged = GddysecFailedLogins::log($title, $password);

        // Check if the quantity of failed logins will be considered as a brute-force attack.
        if ($logged) {
            $failed_logins = GddysecFailedLogins::getData();

            if ($failed_logins) {
                $max_time = 3600;
                $maximum_failed_logins = GddysecOption::get_option('gddysec_maximum_failed_logins');

                /**
                 * If the time passed is within the hour, and the quantity of failed logins
                 * registered in the datastore file is bigger than the maximum quantity of
                 * failed logins allowed per hour (value configured by the administrator in the
                 * settings page), then send an email notification reporting the event and
                 * specifying that it may be a brute-force attack against the login page.
                 */
                if ($failed_logins['diff_time'] <= $max_time
                    && $failed_logins['count'] >= $maximum_failed_logins
                ) {
                    GddysecFailedLogins::report($failed_logins);
                } elseif ($failed_logins['diff_time'] > $max_time) {
                    /**
                     * If there time passed is superior to the hour, then reset the content of the
                     * datastore file containing the failed logins so far, any entry in that file
                     * will not be considered as part of a brute-force attack (if it exists) because
                     * the time passed between the first and last login attempt is big enough to
                     * mitigate the attack. We will consider the current failed login event as the
                     * first entry of that file in case of future attempts during the next sixty
                     * minutes.
                     */
                    GddysecFailedLogins::reset();
                    GddysecFailedLogins::log($title);
                }
            }
        }
    }

    /**
     * Fires immediately after a comment is inserted into the database.
     *
     * The action comment-post can also be used to track the insertion of data in
     * the comments table, but this only returns the identifier of the new entry in
     * the database and the status (approved, not approved, spam). The WP-Insert-
     * Comment action returns the same identifier and additionally the full data set
     * with the comment information.
     *
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_insert_comment
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference/comment_post
     *
     * @param  integer $id      The comment identifier.
     * @param  object  $comment The comment object.
     * @return void
     */
    public static function hook_wp_insert_comment($id = 0, $comment = false)
    {
        if ($comment instanceof stdClass
            && property_exists($comment, 'comment_ID')
            && property_exists($comment, 'comment_agent')
            && property_exists($comment, 'comment_author_IP')
            && GddysecOption::is_enabled(':comment_monitor')
        ) {
            $data_set = array(
                'id' => $comment->comment_ID,
                'post_id' => $comment->comment_post_ID,
                'user_id' => $comment->user_id,
                'parent' => $comment->comment_parent,
                'approved' => $comment->comment_approved,
                'remote_addr' => $comment->comment_author_IP,
                'author_email' => $comment->comment_author_email,
                'date' => $comment->comment_date,
                'content' => $comment->comment_content,
                'user_agent' => $comment->comment_agent,
            );
            $message = base64_encode(json_encode($data_set));
            self::report_notice_event('Base64:' . $message, true);
        }
    }

    // TODO: Log when the comment status is modified: wp_set_comment_status
    // TODO: Log when the comment data is modified: edit_comment
    // TODO: Log when the comment is going to be deleted: delete_comment, trash_comment
    // TODO: Log when the comment is finally deleted: deleted_comment, trashed_comment
    // TODO: Log when the comment is closed: comment_closed
    // TODO: Detect auto updates in core, themes, and plugin files.

    /**
     * Placeholder for arbitrary actions.
     *
     * @return void
     */
    public static function hook_all($action = null, $data = false)
    {
        global $wp_filter, $wp_actions;

        if (is_array($wp_filter)
            && is_array($wp_actions)
            && array_key_exists($action, $wp_actions)
            && !array_key_exists($action, $wp_filter)
            && (
                substr($action, 0, 11) === 'admin_post_'
                || substr($action, 0, 8) === 'wp_ajax_'
            )
        ) {
            $message = sprintf('Undefined XHR action %s', $action);
            self::report_error_event($message);
            header('HTTP/1.1 400 Bad Request');
            exit(1);
        }
    }

    /**
     * Send a notifications to the administrator of some specific events that are
     * not triggered through an hooked action, but through a simple request in the
     * admin interface.
     *
     * @return integer Either one or zero representing the success or fail of the operation.
     */
    public static function hook_undefined_actions()
    {

        $plugin_activate_actions = '(activate|deactivate)(\-selected)?';
        $plugin_update_actions = '(upgrade-plugin|do-plugin-upgrade|update-selected)';

        // Plugin activation and/or deactivation.
        if (current_user_can('activate_plugins')
            && (
                GddysecRequest::get_or_post('action', $plugin_activate_actions)
                || GddysecRequest::get_or_post('action2', $plugin_activate_actions)
            )
        ) {
            $plugin_list = array();
            $items_affected = array();

            // Get the action performed through action or action2 params.
            $action_d = GddysecRequest::get_or_post('action');
            if ($action_d == '-1') {
                $action_d = GddysecRequest::get_or_post('action2');
            }
            $action_d .= 'd';

            if (GddysecRequest::get('plugin', '.+')
                && strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false
            ) {
                $plugin_list[] = GddysecRequest::get('plugin');
            } elseif (isset($_POST['checked'])
                && is_array($_POST['checked'])
                && !empty($_POST['checked'])
            ) {
                $plugin_list = GddysecRequest::post('checked', '_array');
                $action_d = str_replace('-selected', '', $action_d);
            }

            foreach ($plugin_list as $plugin) {
                $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

                if (!empty($plugin_info['Name'])
                    && !empty($plugin_info['Version'])
                ) {
                    $items_affected[] = sprintf(
                        '%s (v%s; %s)',
                        self::escape($plugin_info['Name']),
                        self::escape($plugin_info['Version']),
                        self::escape($plugin)
                    );
                }
            }

            // Report activated/deactivated plugins at once.
            if (!empty($items_affected)) {
                $message_tpl = ( count($items_affected) > 1 )
                    ? 'Plugins %s: (multiple entries): %s'
                    : 'Plugin %s: %s';
                $message = sprintf(
                    $message_tpl,
                    $action_d,
                    @implode(',', $items_affected)
                );
                self::report_warning_event($message);
                self::notify_event('plugin_' . $action_d, $message);
            }
        } // Plugin update request.
        elseif (current_user_can('update_plugins')
            && (
                GddysecRequest::get_or_post('action', $plugin_update_actions)
                || GddysecRequest::get_or_post('action2', $plugin_update_actions)
            )
        ) {
            $plugin_list = array();
            $items_affected = array();

            if (GddysecRequest::get('plugin', '.+')
                && strpos($_SERVER['REQUEST_URI'], 'wp-admin/update.php') !== false
            ) {
                $plugin_list[] = GddysecRequest::get('plugin', '.+');
            } elseif (isset($_POST['checked'])
                && is_array($_POST['checked'])
                && !empty($_POST['checked'])
            ) {
                $plugin_list = GddysecRequest::post('checked', '_array');
            }

            foreach ($plugin_list as $plugin) {
                $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

                if (!empty($plugin_info['Name'])
                    && !empty($plugin_info['Version'])
                ) {
                    $items_affected[] = sprintf(
                        '%s (v%s; %s)',
                        self::escape($plugin_info['Name']),
                        self::escape($plugin_info['Version']),
                        self::escape($plugin)
                    );
                }
            }

            // Report updated plugins at once.
            if (!empty($items_affected)) {
                $message_tpl = ( count($items_affected) > 1 )
                    ? 'Plugins updated: (multiple entries): %s'
                    : 'Plugin updated: %s';
                $message = sprintf(
                    $message_tpl,
                    @implode(',', $items_affected)
                );
                self::report_warning_event($message);
                self::notify_event('plugin_updated', $message);
            }
        } // Plugin installation request.
        elseif (current_user_can('install_plugins')
            && GddysecRequest::get('action', '(install|upload)-plugin')
        ) {
            if (isset($_FILES['pluginzip'])) {
                $plugin = self::escape($_FILES['pluginzip']['name']);
            } else {
                $plugin = GddysecRequest::get('plugin', '.+');

                if (!$plugin) {
                    $plugin = 'Unknown';
                }
            }

            $message = 'Plugin installed: ' . self::escape($plugin);
            GddysecEvent::report_warning_event($message);
            self::notify_event('plugin_installed', $message);
        } // Plugin deletion request.
        elseif (current_user_can('delete_plugins')
            && GddysecRequest::post('action', 'delete-selected')
            && GddysecRequest::post('verify-delete', '1')
        ) {
            $plugin_list = GddysecRequest::post('checked', '_array');
            $items_affected = array();

            foreach ((array) $plugin_list as $plugin) {
                $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

                if (!empty($plugin_info['Name'])
                    && !empty($plugin_info['Version'])
                ) {
                    $items_affected[] = sprintf(
                        '%s (v%s; %s)',
                        self::escape($plugin_info['Name']),
                        self::escape($plugin_info['Version']),
                        self::escape($plugin)
                    );
                }
            }

            // Report deleted plugins at once.
            if (!empty($items_affected)) {
                $message_tpl = ( count($items_affected) > 1 )
                    ? 'Plugins deleted: (multiple entries): %s'
                    : 'Plugin deleted: %s';
                $message = sprintf(
                    $message_tpl,
                    @implode(',', $items_affected)
                );
                self::report_warning_event($message);
                self::notify_event('plugin_deleted', $message);
            }
        } // Plugin editor request.
        elseif (current_user_can('edit_plugins')
            && GddysecRequest::post('action', 'update')
            && GddysecRequest::post('plugin', '.+')
            && GddysecRequest::post('file', '.+')
            && strpos($_SERVER['REQUEST_URI'], 'plugin-editor.php') !== false
        ) {
            $filename = GddysecRequest::post('file');
            $message = 'Plugin editor used in: ' . Gddysec::escape($filename);
            self::report_error_event($message);
            self::notify_event('theme_editor', $message);
        } // Theme editor request.
        elseif (current_user_can('edit_themes')
            && GddysecRequest::post('action', 'update')
            && GddysecRequest::post('theme', '.+')
            && GddysecRequest::post('file', '.+')
            && strpos($_SERVER['REQUEST_URI'], 'theme-editor.php') !== false
        ) {
            $theme_name = GddysecRequest::post('theme');
            $filename = GddysecRequest::post('file');
            $message = 'Theme editor used in: ' . Gddysec::escape($theme_name) . '/' . Gddysec::escape($filename);
            self::report_error_event($message);
            self::notify_event('theme_editor', $message);
        } // Theme installation request.
        elseif (current_user_can('install_themes')
            && GddysecRequest::get('action', 'install-theme')
        ) {
            $theme = GddysecRequest::get('theme', '.+');

            if (!$theme) {
                $theme = 'Unknown';
            }

            $message = 'Theme installed: ' . self::escape($theme);
            GddysecEvent::report_warning_event($message);
            self::notify_event('theme_installed', $message);
        } // Theme deletion request.
        elseif (current_user_can('delete_themes')
            && GddysecRequest::get_or_post('action', 'delete')
            && GddysecRequest::get_or_post('stylesheet', '.+')
        ) {
            $theme = GddysecRequest::get('stylesheet', '.+');

            if (!$theme) {
                $theme = 'Unknown';
            }

            $message = 'Theme deleted: ' . self::escape($theme);
            GddysecEvent::report_warning_event($message);
            self::notify_event('theme_deleted', $message);
        } // Theme update request.
        elseif (current_user_can('update_themes')
            && GddysecRequest::get('action', '(upgrade-theme|do-theme-upgrade)')
            && GddysecRequest::post('checked', '_array')
        ) {
            $themes = GddysecRequest::post('checked', '_array');
            $items_affected = array();

            foreach ((array) $themes as $theme) {
                $theme_info = wp_get_theme($theme);
                $theme_name = ucwords($theme);
                $theme_version = '0.0';

                if ($theme_info->exists()) {
                    $theme_name = $theme_info->get('Name');
                    $theme_version = $theme_info->get('Version');
                }

                $items_affected[] = sprintf(
                    '%s (v%s; %s)',
                    self::escape($theme_name),
                    self::escape($theme_version),
                    self::escape($theme)
                );
            }

            // Report updated themes at once.
            if (!empty($items_affected)) {
                $message_tpl = ( count($items_affected) > 1 )
                    ? 'Themes updated: (multiple entries): %s'
                    : 'Theme updated: %s';
                $message = sprintf(
                    $message_tpl,
                    @implode(',', $items_affected)
                );
                self::report_warning_event($message);
                self::notify_event('theme_updated', $message);
            }
        } // WordPress update request.
        elseif (current_user_can('update_core')
            && GddysecRequest::get('action', '(do-core-upgrade|do-core-reinstall)')
            && GddysecRequest::post('upgrade')
        ) {
            $message = 'WordPress updated to version: ' . GddysecRequest::post('version');
            self::report_critical_event($message);
            self::notify_event('website_updated', $message);
        } // Widget addition or deletion.
        elseif (current_user_can('edit_theme_options')
            && GddysecRequest::post('action', 'save-widget')
            && GddysecRequest::post('id_base') !== false
            && GddysecRequest::post('sidebar') !== false
        ) {
            if (GddysecRequest::post('delete_widget', '1')) {
                $action_d = 'deleted';
                $action_text = 'deleted from';
            } else {
                $action_d = 'added';
                $action_text = 'added to';
            }

            $message = sprintf(
                'Widget %s (%s) %s %s (#%d; size %dx%d)',
                GddysecRequest::post('id_base'),
                GddysecRequest::post('widget-id'),
                $action_text,
                GddysecRequest::post('sidebar'),
                GddysecRequest::post('widget_number'),
                GddysecRequest::post('widget-width'),
                GddysecRequest::post('widget-height')
            );

            self::report_warning_event($message);
            self::notify_event('widget_' . $action_d, $message);
        } // Detect any Wordpress settings modification.
        elseif (current_user_can('manage_options')
            && GddysecOption::check_options_nonce()
        ) {
            // Get the settings available in the database and compare them with the submission.
            $options_changed = GddysecOption::what_options_were_changed($_POST);
            $options_changed_str = '';
            $options_changed_simple = '';
            $options_changed_count = 0;

            // Generate the list of options changed.
            foreach ($options_changed['original'] as $option_name => $option_value) {
                $options_changed_count += 1;
                $options_changed_str .= sprintf(
                    "The value of the option <b>%s</b> was changed from <b>'%s'</b> to <b>'%s'</b>.<br>\n",
                    self::escape($option_name),
                    self::escape($option_value),
                    self::escape($options_changed['changed'][ $option_name ])
                );
                $options_changed_simple .= sprintf(
                    "%s: from '%s' to '%s',",
                    self::escape($option_name),
                    self::escape($option_value),
                    self::escape($options_changed['changed'][ $option_name ])
                );
            }

            // Get the option group (name of the page where the request was originated).
            $option_page = isset($_POST['option_page']) ? $_POST['option_page'] : 'options';
            $page_referer = false;

            // Check which of these option groups where modified.
            switch ($option_page) {
                case 'options':
                    $page_referer = 'Global';
                    break;
                case 'general':    /* no_break */
                case 'writing':    /* no_break */
                case 'reading':    /* no_break */
                case 'discussion': /* no_break */
                case 'media':      /* no_break */
                case 'permalink':
                    $page_referer = ucwords($option_page);
                    break;
                default:
                    $page_referer = 'Common';
                    break;
            }

            if ($page_referer && $options_changed_count > 0) {
                $message = $page_referer . ' settings changed';
                GddysecEvent::report_error_event(sprintf(
                    '%s: (multiple entries): %s',
                    $message,
                    rtrim($options_changed_simple, ',')
                ));
                self::notify_event('settings_updated', $message . "<br>\n" . $options_changed_str);
            }
        }

    }
}
