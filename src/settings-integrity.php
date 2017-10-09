<?php

/**
 * Code related to the settings-integrity.php interface.
 *
 * PHP version 5
 *
 * @category   Library
 * @package    GoDaddy
 * @subpackage GoDaddySecurity
 * @author     Daniel Cid <dcid@sucuri.net>
 * @copyright  2017 Sucuri Inc. - GoDaddy Inc.
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GPL2
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
 * Settings for the WordPress integrity scanner.
 *
 * Generates the HTML code to display a list of options in the settings page to
 * allow the website owner to configure the functionality of the WordPress core
 * integrity scanner and the optional Unix diff utility. This also includes some
 * options to configure the website installation language and the false positive
 * cache file.
 *
 * @category   Library
 * @package    GoDaddy
 * @subpackage GoDaddySecurity
 * @author     Daniel Cid <dcid@sucuri.net>
 * @copyright  2017 Sucuri Inc. - GoDaddy Inc.
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GPL2
 * @link       https://wordpress.org/plugins/godaddy-security
 */
class GddysecSettingsIntegrity extends GddysecSettings
{
    /**
     * Configures the diffUtility for the integrity scanner.
     *
     * @param  bool $nonce True if the CSRF protection worked, false otherwise.
     * @return string      HTML code to render the configuration panel.
     */
    public static function diffUtility($nonce)
    {
        $params = array();

        $params['DiffUtility.StatusNum'] = 0;
        $params['DiffUtility.Status'] = 'Disabled';
        $params['DiffUtility.SwitchText'] = 'Enable';
        $params['DiffUtility.SwitchValue'] = 'enable';

        if ($nonce) {
            // Enable or disable the Unix diff utility.
            $status = GddysecRequest::post(':diff_utility', '(en|dis)able');

            if ($status) {
                if (!GddysecCommand::exists('diff')) {
                    GddysecInterface::error('Your hosting provider has blocked the execution of external commands.');
                } else {
                    $status = $status . 'd'; /* add past tense */
                    $message = 'Integrity diff utility has been <code>' . $status . '</code>';

                    GddysecOption::updateOption(':diff_utility', $status);
                    GddysecEvent::reportInfoEvent($message);
                    GddysecEvent::notifyEvent('plugin_change', $message);
                    GddysecInterface::info('The status of the integrity diff utility has been changed');
                }
            }
        }

        if (GddysecOption::isEnabled(':diff_utility')) {
            $params['DiffUtility.StatusNum'] = 1;
            $params['DiffUtility.Status'] = 'Enabled';
            $params['DiffUtility.SwitchText'] = 'Disable';
            $params['DiffUtility.SwitchValue'] = 'disable';
        }

        return GddysecTemplate::getSection('settings-scanner-integrity-diff-utility', $params);
    }

    /**
     * Configures the cache for the integrity scanner.
     *
     * @param  bool $nonce True if the CSRF protection worked, false otherwise.
     * @return string      HTML code to render the configuration panel.
     */
    public static function cache($nonce)
    {
        $params = array();
        $cache = new GddysecCache('integrity');
        $fpath = Gddysec::dataStorePath('gddysec-integrity.php');

        if ($nonce && GddysecRequest::post(':reset_integrity_cache')) {
            $deletedFiles = array();
            $files = GddysecRequest::post(':corefile_path', '_array');

            foreach ($files as $path) {
                if ($cache->delete(md5($path))) {
                    $deletedFiles[] = $path;
                }
            }

            if (!empty($deletedFiles)) {
                GddysecEvent::reportDebugEvent(
                    'Core files that will not be ignored anymore: (mul'
                    . 'tiple entries): ' . implode(',', $deletedFiles)
                );
                GddysecInterface::info('The selected files have been successfully processed.');
            }
        }

        $params['IgnoredFiles'] = '';
        $params['CacheSize'] = Gddysec::humanFileSize(@filesize($fpath));
        $params['CacheLifeTime'] = GDDYSEC_SITECHECK_LIFETIME;
        $params['NoFilesVisibility'] = 'visible';

        $ignored_files = $cache->getAll();

        if (is_array($ignored_files) && !empty($ignored_files)) {
            $params['NoFilesVisibility'] = 'hidden';

            foreach ($ignored_files as $hash => $data) {
                $params['IgnoredFiles'] .= GddysecTemplate::getSnippet(
                    'settings-scanner-integrity-cache',
                    array(
                        'UniqueId' => substr($hash, 0, 8),
                        'FilePath' => $data->file_path,
                        'StatusType' => $data->file_status,
                        'IgnoredAt' => Gddysec::datetime($data->ignored_at),
                    )
                );
            }
        }

        return GddysecTemplate::getSection('settings-scanner-integrity-cache', $params);
    }
}
