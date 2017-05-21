<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Read and parse the content of the notification settings template.
 *
 * @return string Parsed HTML code for the notification settings panel.
 */
function gddysec_settings_alert($nonce)
{
    $params = array();

    $params['Settings.Recipients'] = gddysec_settings_alert_recipients($nonce);
    $params['Settings.Subject'] = gddysec_settings_alert_subject($nonce);
    $params['Settings.PerHour'] = gddysec_settings_alert_perhour($nonce);
    $params['Settings.BruteForce'] = gddysec_settings_alert_bruteforce($nonce);
    $params['Settings.Events'] = gddysec_settings_alert_events($nonce);
    $params['Settings.IgnoreRules'] = gddysec_settings_alert_ignore_rules($nonce);

    return GddysecTemplate::getSection('settings-alert', $params);
}

function gddysec_settings_alert_recipients($nonce)
{
    $params = array();
    $params['Settings.Recipients'] = '';
    $notify_to = GddysecOption::get_option(':notify_to');
    $emails = array();

    // If the recipient list is not empty, explode.
    if (is_string($notify_to)) {
        $emails = explode(',', $notify_to);
    }

    // Process form submission.
    if ($nonce) {
        // Add new email address to the alert recipient list.
        if (GddysecRequest::post(':save_recipient') !== false) {
            $new_email = GddysecRequest::post(':recipient');

            if (Gddysec::is_valid_email($new_email)) {
                $emails[] = $new_email;
                $message = 'Plugin will send email alerts to: <code>' . $new_email . '</code>';

                GddysecOption::update_option(':notify_to', implode(',', $emails));
                GddysecEvent::report_info_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            } else {
                GddysecInterface::error('Email format not supported.');
            }
        }

        // Delete one or more recipients from the list.
        if (GddysecRequest::post(':delete_recipients') !== false) {
            $deleted_emails = array();
            $recipients = GddysecRequest::post(':recipients', '_array');

            foreach ($recipients as $address) {
                if (in_array($address, $emails)) {
                    $deleted_emails[] = $address;
                    $index = array_search($address, $emails);
                    unset($emails[$index]);
                }
            }

            if (!empty($deleted_emails)) {
                $deleted_emails_str = implode(",\x20", $deleted_emails);
                $message = 'Plugin will not send email alerts to: <code>' . $deleted_emails_str . '</code>';

                GddysecOption::update_option(':notify_to', implode(',', $emails));
                GddysecEvent::report_info_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            }
        }

        // Debug ability of the plugin to send email alerts correctly.
        if (GddysecRequest::post(':debug_email')) {
            $recipients = GddysecOption::get_option(':notify_to');
            GddysecMail::send_mail(
                $recipients,
                'Test Email Alert',
                sprintf('Test email alert sent at %s', date('r')),
                array('Force' => true)
            );
            GddysecInterface::info('Test email alert sent, check your inbox.');
        }
    }

    $counter = 0;

    foreach ($emails as $email) {
        if (!empty($email)) {
            $css_class = ($counter % 2 === 0) ? '' : 'alternate';
            $params['Settings.Recipients'] .= GddysecTemplate::getSnippet(
                'settings-alert-recipients',
                array(
                    'Recipient.CssClass' => $css_class,
                    'Recipient.Email' => $email,
                )
            );
            $counter++;
        }
    }

    return GddysecTemplate::getSection('settings-alert-recipients', $params);
}

function gddysec_settings_alert_subject($nonce)
{
    global $gddysec_email_subjects;

    $params = array(
        'Settings.Subject' => '',
        'Settings.CustomChecked' => '',
        'Settings.CustomValue' => '',
    );

    // Process form submission to change the alert settings.
    if ($nonce) {
        if ($email_subject = GddysecRequest::post(':email_subject')) {
            $current_value = GddysecOption::get_option(':email_subject');
            $new_email_subject = false;

            /**
             * Validate the format of the email subject format.
             *
             * If the user chooses the option to build the subject of the email alerts
             * manually we will need to validate the characters. Otherwise we will need to
             * check if the pseudo-tags selected by the user are allowed and supported.
             */
            if ($email_subject === 'custom') {
                $format_pattern = '/^[0-9a-zA-Z:,\s]+$/';
                $custom_subject = GddysecRequest::post(':custom_email_subject');

                if ($custom_subject !== false
                    && !empty($custom_subject)
                    && @preg_match($format_pattern, $custom_subject)
                ) {
                    $new_email_subject = trim($custom_subject);
                } else {
                    GddysecInterface::error('Invalid characters in the email subject.');
                }
            } elseif (is_array($gddysec_email_subjects)
                && in_array($email_subject, $gddysec_email_subjects)
            ) {
                $new_email_subject = trim($email_subject);
            }

            // Proceed with the operation saving the new subject.
            if ($new_email_subject !== false
                && $current_value !== $new_email_subject
            ) {
                $message = 'Email subject set to <code>' . $new_email_subject . '</code>';

                GddysecOption::update_option(':email_subject', $new_email_subject);
                GddysecEvent::report_info_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            }
        }
    }

    // Build the HTML code for the interface.
    if (is_array($gddysec_email_subjects)) {
        $email_subject = GddysecOption::get_option(':email_subject');
        $is_official_subject = false;

        foreach ($gddysec_email_subjects as $subject_format) {
            if ($email_subject === $subject_format) {
                $is_official_subject = true;
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }

            $params['Settings.Subject'] .= GddysecTemplate::getSnippet(
                'settings-alert-subject',
                array(
                    'EmailSubject.Name' => $subject_format,
                    'EmailSubject.Value' => $subject_format,
                    'EmailSubject.Checked' => $checked,
                )
            );
        }

        if ($is_official_subject === false) {
            $params['Settings.CustomChecked'] = 'checked="checked"';
            $params['Settings.CustomValue'] = $email_subject;
        }
    }

    return GddysecTemplate::getSection('settings-alert-subject', $params);
}

function gddysec_settings_alert_perhour($nonce)
{
    global $gddysec_emails_per_hour;

    $params = array();
    $params['Settings.PerHour'] = '';

    if ($nonce) {
        // Update the value for the maximum emails per hour.
        if ($per_hour = GddysecRequest::post(':emails_per_hour')) {
            if (array_key_exists($per_hour, $gddysec_emails_per_hour)) {
                $per_hour_label = strtolower($gddysec_emails_per_hour[$per_hour]);
                $message = 'Maximum alerts per hour set to <code>' . $per_hour_label . '</code>';

                GddysecOption::update_option(':emails_per_hour', $per_hour);
                GddysecEvent::report_info_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            } else {
                GddysecInterface::error('Invalid value for the maximum emails per hour.');
            }
        }
    }

    $per_hour = GddysecOption::get_option(':emails_per_hour');
    $per_hour_options = GddysecTemplate::selectOptions($gddysec_emails_per_hour, $per_hour);
    $params['Settings.PerHour'] = $per_hour_options;

    return GddysecTemplate::getSection('settings-alert-perhour', $params);
}

function gddysec_settings_alert_bruteforce($nonce)
{
    global $gddysec_maximum_failed_logins;

    $params = array();
    $params['Settings.BruteForce'] = '';

    if ($nonce) {
        // Update the maximum failed logins per hour before consider it a brute-force attack.
        if ($maximum = GddysecRequest::post(':maximum_failed_logins')) {
            if (array_key_exists($maximum, $gddysec_maximum_failed_logins)) {
                $message = 'Consider brute-force attack after <code>' . $maximum . '</code> failed logins per hour';

                GddysecOption::update_option(':maximum_failed_logins', $maximum);
                GddysecEvent::report_info_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            } else {
                GddysecInterface::error('Invalid value for the brute-force alerts.');
            }
        }
    }

    $maximum = GddysecOption::get_option(':maximum_failed_logins');
    $maximum_options = GddysecTemplate::selectOptions($gddysec_maximum_failed_logins, $maximum);
    $params['Settings.BruteForce'] = $maximum_options;

    return GddysecTemplate::getSection('settings-alert-bruteforce', $params);
}

function gddysec_settings_alert_events($nonce)
{
    global $gddysec_notify_options;

    $params = array();
    $params['Settings.Events'] = '';

    // Process form submission to change the alert settings.
    if ($nonce) {
        // Update the notification settings.
        if (GddysecRequest::post(':save_alert_events') !== false) {
            $ucounter = 0;

            foreach ($gddysec_notify_options as $alert_type => $alert_label) {
                $option_value = GddysecRequest::post($alert_type, '(1|0)');

                if ($option_value !== false) {
                    $current_value = GddysecOption::get_option($alert_type);
                    $option_value = ($option_value == 1) ? 'enabled' : 'disabled';

                    // Check that the option value was actually changed.
                    if ($current_value !== $option_value) {
                        $written = GddysecOption::update_option($alert_type, $option_value);

                        if ($written === true) {
                            $ucounter += 1;
                        }
                    }
                }
            }

            if ($ucounter > 0) {
                $message = 'A total of ' . $ucounter . ' alert events were changed';

                GddysecEvent::report_info_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            }
        }
    }

    // Build the HTML code for the interface.
    if (is_array($gddysec_notify_options)) {
        $pattern = '/^([a-z]+:)?(.+)/';
        $counter = 0;

        foreach ($gddysec_notify_options as $alert_type => $alert_label) {
            $alert_value = GddysecOption::get_option($alert_type);
            $checked = ($alert_value == 'enabled') ? 'checked="checked"' : '';
            $css_class = ($counter % 2 === 0) ? 'alternate' : '';
            $alert_icon = '';

            if (@preg_match($pattern, $alert_label, $match)) {
                $alert_group = str_replace(':', '', $match[1]);
                $alert_label = $match[2];

                switch ($alert_group) {
                    case 'user':
                        $alert_icon = 'dashicons-before dashicons-admin-users';
                        break;
                    case 'plugin':
                        $alert_icon = 'dashicons-before dashicons-admin-plugins';
                        break;
                    case 'theme':
                        $alert_icon = 'dashicons-before dashicons-admin-appearance';
                        break;
                }
            }

            $params['Settings.Events'] .= GddysecTemplate::getSnippet(
                'settings-alert-events',
                array(
                    'Event.CssClass' => $css_class,
                    'Event.Name' => $alert_type,
                    'Event.Checked' => $checked,
                    'Event.Label' => $alert_label,
                    'Event.LabelIcon' => $alert_icon,
                )
            );
            $counter++;
        }
    }

    return GddysecTemplate::getSection('settings-alert-events', $params);
}

function gddysec_settings_alert_ignore_rules()
{
    $notify_new_site_content = GddysecOption::get_option(':notify_post_publication');

    $template_variables = array(
        'IgnoreRules.TableVisibility' => 'hidden',
        'IgnoreRules.PostTypes' => '',
    );

    if ($notify_new_site_content == 'enabled') {
        $post_types = get_post_types();
        $ignored_events = GddysecOption::getIgnoredEvents();

        $template_variables['IgnoreRules.TableVisibility'] = 'visible';
        $counter = 0;

        foreach ($post_types as $post_type => $post_type_object) {
            $counter++;
            $css_class = ($counter % 2 === 0) ? 'alternate' : '';
            $post_type_title = ucwords(str_replace('_', chr(32), $post_type));

            if (array_key_exists($post_type, $ignored_events)) {
                $is_ignored_text = 'YES';
                $was_ignored_at = Gddysec::datetime($ignored_events[ $post_type ]);
                $is_ignored_class = 'danger';
                $button_action = 'remove';
                $button_class = 'button-primary';
                $button_text = 'Allow';
            } else {
                $is_ignored_text = 'NO';
                $was_ignored_at = 'Not ignored';
                $is_ignored_class = 'success';
                $button_action = 'add';
                $button_class = 'button-primary button-danger';
                $button_text = 'Ignore';
            }

            $template_variables['IgnoreRules.PostTypes'] .= GddysecTemplate::getSnippet(
                'settings-alert-ignorerules',
                array(
                    'IgnoreRules.CssClass' => $css_class,
                    'IgnoreRules.Num' => $counter,
                    'IgnoreRules.PostTypeTitle' => $post_type_title,
                    'IgnoreRules.IsIgnored' => $is_ignored_text,
                    'IgnoreRules.WasIgnoredAt' => $was_ignored_at,
                    'IgnoreRules.IsIgnoredClass' => $is_ignored_class,
                    'IgnoreRules.PostType' => $post_type,
                    'IgnoreRules.Action' => $button_action,
                    'IgnoreRules.ButtonClass' => 'button ' . $button_class,
                    'IgnoreRules.ButtonText' => $button_text,
                )
            );
        }
    }

    return GddysecTemplate::getSection('settings-alert-ignorerules', $template_variables);
}
