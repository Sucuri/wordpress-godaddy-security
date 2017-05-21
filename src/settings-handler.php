<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Process the requests sent by the form submissions originated in the settings
 * page, all forms must have a nonce field that will be checked against the one
 * generated in the template render function.
 *
 * @param  boolean $page_nonce True if the nonce is valid, False otherwise.
 * @return void
 */
function gddysec_settings_form_submissions($page_nonce = null)
{
    // Use this conditional to avoid double checking.
    if (is_null($page_nonce)) {
        $page_nonce = GddysecInterface::check_nonce();
    }

    if ($page_nonce) {
        // Ignore a new event for email notifications.
        if ($action = GddysecRequest::post(':ignorerule_action', '(add|remove)')) {
            $ignore_rule = GddysecRequest::post(':ignorerule');

            if ($action == 'add') {
                if (GddysecOption::addIgnoredEvent($ignore_rule)) {
                    GddysecInterface::info('Post-type ignored successfully.');
                    GddysecEvent::report_warning_event('Changes in <code>' . $ignore_rule . '</code> post-type will be ignored');
                } else {
                    GddysecInterface::error('The post-type is invalid or it may be already ignored.');
                }
            } elseif ($action == 'remove') {
                GddysecOption::removeIgnoredEvent($ignore_rule);
                GddysecInterface::info('Post-type removed from the list successfully.');
                GddysecEvent::report_notice_event('Changes in <code>' . $ignore_rule . '</code> post-type will not be ignored');
            }
        }
    }
}
