<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Miscellaneous library.
 *
 * Multiple and generic functions that will be used through out the code of
 * other libraries extending from this and functions defined in other files, be
 * aware of the hierarchy and check the other libraries for duplicated methods.
 */
class Gddysec
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
    }

    /**
     * Throw generic exception instead of silent failure for unit-tests.
     *
     * @param  string $message Error or information message.
     * @param  string $type    Either info or error.
     * @return void
     */
    public static function throwException($message, $type = 'error')
    {
        if (defined('GDDYSEC_THROW_EXCEPTIONS')
            && GDDYSEC_THROW_EXCEPTIONS === true
            && is_string($message)
            && !empty($message)
        ) {
            $code = ($type === 'error' ? 157 : 333);
            $message = str_replace(
                '<b>GoDaddy Security:</b>',
                ($type === 'error' ? 'Error:' : 'Info:'),
                $message
            );

            throw new Exception($message, $code);
        }
    }

    /**
     * Return name of a variable with the plugin's prefix (if needed).
     *
     * To facilitate the development, you can prefix the name of the key in the
     * request (when accessing it) with a single colon, this function will
     * automatically replace that character with the unique identifier of the
     * plugin.
     *
     * @param  string $var_name Name of a variable with an optional colon at the beginning.
     * @return string           Full name of the variable with the extra characters (if needed).
     */
    public static function variable_prefix($var_name = '')
    {
        if (!empty($var_name) && $var_name[0] === ':') {
            $var_name = sprintf(
                '%s_%s',
                GDDYSEC,
                substr($var_name, 1)
            );
        }

        return $var_name;
    }

    /**
     * Encodes the less-than, greater-than, ampersand, double quote and single quote
     * characters, will never double encode entities.
     *
     * @param  string $text The text which is to be encoded.
     * @return string       The encoded text with HTML entities.
     */
    public static function escape($text = '')
    {
        // Escape the value of the variable using a built-in function if possible.
        if (function_exists('esc_attr')) {
            $text = esc_attr($text);
        } else {
            $text = htmlspecialchars($text);
        }

        return $text;
    }

    /**
     * Translate a given number in bytes to a human readable file size using the
     * a approximate value in Kylo, Mega, Giga, etc.
     *
     * @link   https://www.php.net/manual/en/function.filesize.php#106569
     * @param  integer $bytes    An integer representing a file size in bytes.
     * @param  integer $decimals How many decimals should be returned after the translation.
     * @return string            Human readable representation of the given number in Kylo, Mega, Giga, etc.
     */
    public static function human_filesize($bytes = 0, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[ $factor ];
    }

    /**
     * Check if the admin init hook must not be intercepted.
     *
     * @return boolean True if the admin init hook must not be intercepted.
     */
    public static function noAdminInit()
    {
        return (bool) (
            defined('GDDYSEC_ADMIN_INIT')
            && GDDYSEC_ADMIN_INIT === false
        );
    }

    /**
     * Check if the admin init hook must be intercepted.
     *
     * @return boolean True if the admin init hook must be intercepted.
     */
    public static function runAdminInit()
    {
        return (bool) (self::noAdminInit() === false);
    }

    /**
     * Fix the deliminar of a resource path.
     *
     * In Windows based system the directory separator is a back slash which
     * differs from what other file systems use. To keep consistency during the
     * unit-tests we have decided to replace any non forward slash with it.
     *
     * @return string Fixed file path.
     */
    public static function fixPath($path = '')
    {
        $delimiter = '/' /* Forward slash */;
        $path = str_replace(DIRECTORY_SEPARATOR, $delimiter, $path);
        $path = rtrim($path, $delimiter);

        return $path;
    }

    /**
     * Returns the system filepath to the relevant user uploads directory for this
     * site. This is a multisite capable function.
     *
     * @param  string $path The relative path that needs to be completed to get the absolute path.
     * @return string       The full filesystem path including the directory specified.
     */
    public static function datastore_folder_path($path = '')
    {
        $datastore = GddysecOption::get_option(':datastore_path');

        return self::fixPath($datastore . '/' . $path);
    }

    /**
     * Check whether the current site is working as a multi-site instance.
     *
     * @return boolean Either TRUE or FALSE in case WordPress is being used as a multi-site instance.
     */
    public static function is_multisite()
    {
        return (bool) (function_exists('is_multisite') && is_multisite());
    }

    public static function admin_url($url = '')
    {
        if (self::is_multisite()) {
            return network_admin_url($url);
        } else {
            return admin_url($url);
        }
    }

    /**
     * Find and retrieve the current version of Wordpress installed.
     *
     * @return string The version number of Wordpress installed.
     */
    public static function site_version()
    {
        global $wp_version;

        if ($wp_version === null) {
            $wp_version_path = ABSPATH . WPINC . '/version.php';

            if (file_exists($wp_version_path)) {
                include($wp_version_path);
                $wp_version = isset($wp_version) ? $wp_version : '0.0';
            } else {
                $option_version = get_option('version');
                $wp_version = $option_version ? $option_version : '0.0';
            }
        }

        return self::escape($wp_version);
    }

    /**
     * Execute the plugin' scheduled tasks.
     *
     * @return void
     */
    public static function runScheduledTask()
    {
        GddysecEvent::filesystem_scan();
        GddysecSiteCheck::scanAndCollectData();
        GddysecCoreFiles::getCoreFilesStatus(true);
    }

    /**
     * List of allowed HTTP headers to retrieve the real IP.
     *
     * Once the DNS lookups are enabled to discover the real IP address of the
     * visitors the user may choose the HTTP header that will be used by default to
     * retrieve the real IP address of each HTTP request, generally they do not need
     * to set this but in rare cases the hosting provider may have a load balancer
     * that can interfere in the process, in which case they will have to explicitly
     * specify the main HTTP header. This is a list of the allowed headers that the
     * user can choose.
     *
     * @param  boolean $with_keys Return the array with its values are keys.
     * @return array              Allowed HTTP headers to retrieve real IP.
     */
    public static function allowedHttpHeaders($with_keys = false)
    {
        $allowed = array(
            /* CloudProxy custom HTTP headers */
            'HTTP_X_GDDYSEC_CLIENTIP',
            /* CloudFlare custom HTTP headers */
            'HTTP_CF_CONNECTING_IP', /* Real visitor IP. */
            'HTTP_CF_IPCOUNTRY', /* Country of visitor. */
            'HTTP_CF_RAY', /* https://support.cloudflare.com/entries/23046742-w. */
            'HTTP_CF_VISITOR', /* Determine if HTTP or HTTPS. */
            /* Possible HTTP headers */
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'GDDYSEC_RIP',
            'REMOTE_ADDR',
        );

        if ($with_keys === true) {
            $verbose = array();

            foreach ($allowed as $header) {
                $verbose[$header] = $header;
            }

            return $verbose;
        }

        return $allowed;
    }

    /**
     * List HTTP headers ordered.
     *
     * The list of HTTP headers is ordered per relevancy, and having the main HTTP
     * header as the first entry, this guarantees that the IP address of the
     * visitors will be retrieved from the HTTP header chosen by the user first and
     * fallback to the other alternatives if available.
     *
     * @return array Ordered allowed HTTP headers.
     */
    private static function orderedHttpHeaders()
    {
        $ordered = array();
        $allowed = self::allowedHttpHeaders();
        $addr_header = GddysecOption::get_option(':addr_header');
        $ordered[] = $addr_header;

        foreach ($allowed as $header) {
            if (!in_array($header, $ordered)) {
                $ordered[] = $header;
            }
        }

        return $ordered;
    }

    /**
     * Retrieve the real ip address of the user in the current request.
     *
     * @param  boolean $with_header Return HTTP header where the IP address was found.
     * @return string               Real IP address of the user in the current request.
     */
    public static function remoteAddr($with_header = false)
    {
        $remote_addr = false;
        $header_used = 'unknown';
        $headers = self::orderedHttpHeaders();

        foreach ($headers as $header) {
            if (array_key_exists($header, $_SERVER)
                && self::is_valid_ip($_SERVER[$header])
            ) {
                $remote_addr = $_SERVER[$header];
                $header_used = $header;
                break;
            }
        }

        if (!$remote_addr || $remote_addr === '::1') {
            $remote_addr = '127.0.0.1';
        }

        if ($with_header) {
            return $header_used;
        }

        return $remote_addr;
    }

    /**
     * Return the HTTP header used to retrieve the remote address.
     *
     * @return string The HTTP header used to retrieve the remote address.
     */
    public static function remoteAddrHeader()
    {
        return self::remoteAddr(true);
    }

    /**
     * Retrieve the user-agent from the current request.
     *
     * @return string The user-agent from the current request.
     */
    public static function userAgent()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return '-' /* empty user-agent */;
        }

        return self::escape($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Get the clean version of the current domain.
     *
     * @return string The domain of the current site.
     */
    public static function get_domain($return_tld = false)
    {
        if (function_exists('get_site_url')) {
            $site_url = get_site_url();
            $pattern = '/([fhtps]+:\/\/)?([^:\/]+)(:[0-9:]+)?(\/.*)?/';
            $replacement = ($return_tld === true) ? '$2' : '$2$3$4';
            $domain_name = @preg_replace($pattern, $replacement, $site_url);

            return $domain_name;
        }

        return false;
    }

    /**
     * Get top-level domain (TLD) of the website.
     *
     * @return string Top-level domain (TLD) of the website.
     */
    public static function get_top_level_domain()
    {
        return self::get_domain(true);
    }

    /**
     * Get the email address set by the administrator to receive the notifications
     * sent by the plugin, if the email is missing the WordPress email address is
     * chosen by default.
     *
     * @return string The administrator email address.
     */
    public static function get_site_email()
    {
        $email = get_option('admin_email');

        if (self::is_valid_email($email)) {
            return $email;
        }

        return false;
    }

    /**
     * Get user data by field and data.
     *
     * @param  integer $identifier User account identifier.
     * @return object              WordPress user object with data.
     */
    public static function get_user_by_id($identifier = 0)
    {
        if (function_exists('get_user_by')) {
            $user = get_user_by('id', $identifier);

            if ($user instanceof WP_User) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Retrieve a list of all admin user accounts.
     *
     * @return array List of admin users, false otherwise.
     */
    public static function get_admin_users()
    {
        if (function_exists('get_users')) {
            return get_users(array('role' => 'administrator'));
        }

        return false;
    }

    /**
     * Get a list of user emails that can be used to generate an API key for this
     * website. Only accounts with the status in zero will be returned, the status
     * field in the users table is officially deprecated but some 3rd-party plugins
     * still use it to check if the account was activated by the owner of the email,
     * a value different than zero generally means that the email was not verified
     * successfully.
     *
     * @return array List of user identifiers and email addresses.
     */
    public static function get_users_for_api_key()
    {
        $valid_users = array();
        $users = self::get_admin_users();

        if ($users !== false) {
            foreach ($users as $user) {
                if ($user->user_status === '0') {
                    $valid_users[$user->ID] = sprintf(
                        '%s - %s',
                        $user->user_login,
                        $user->user_email
                    );
                }
            }
        }

        return $valid_users;
    }

    /**
     * Retrieve the date in localized format, based on timestamp.
     *
     * If the locale specifies the locale month and weekday, then the locale will
     * take over the format for the date. If it isn't, then the date format string
     * will be used instead.
     *
     * @param  integer $timestamp Unix timestamp.
     * @return string             The date, translated if locale specifies it.
     */
    public static function datetime($timestamp = null)
    {
        global $gddysec_date_format, $gddysec_time_format;

        $tz_format = $gddysec_date_format . "\x20" . $gddysec_time_format;

        if (is_numeric($timestamp) && $timestamp > 0) {
            return date_i18n($tz_format, $timestamp);
        }

        return date_i18n($tz_format);
    }

    /**
     * Retrieve the date in localized format based on the current time.
     *
     * @return string The date, translated if locale specifies it.
     */
    public static function current_datetime()
    {
        return self::datetime();
    }

    /**
     * Check whether an IP address has a valid format or not.
     *
     * @param  string  $remote_addr The host IP address.
     * @return boolean              Whether the IP address specified is valid or not.
     */
    public static function is_valid_ip($remote_addr = '')
    {
        if (function_exists('filter_var')) {
            return (bool) filter_var($remote_addr, FILTER_VALIDATE_IP);
        } elseif (strlen($remote_addr) >= 7) {
            $pattern = '/^([0-9]{1,3}\.) {3}[0-9]{1,3}$/';

            if (preg_match($pattern, $remote_addr, $match)) {
                for ($i = 0; $i < 4; $i++) {
                    if ($match[ $i ] > 255) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Validate email address.
     *
     * This use the native PHP function filter_var which is available in PHP >=
     * 5.2.0 if it is not found in the interpreter this function will sue regular
     * expressions to check whether the email address passed is valid or not.
     *
     * @see https://www.php.net/manual/en/function.filter-var.php
     *
     * @param  string $email The string that will be validated as an email address.
     * @return boolean       TRUE if the email address passed to the function is valid, FALSE if not.
     */
    public static function is_valid_email($email = '')
    {
        if (function_exists('filter_var')) {
            return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        } else {
            $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';
            return (bool) preg_match($pattern, $email);
        }
    }

    /**
     * Returns list of supported languages.
     *
     * @return array Supported languages abbreviated.
     */
    public static function languages()
    {
        return array(
            'af' => 'af',
            'ak' => 'ak',
            'sq' => 'sq',
            'arq' => 'arq',
            'am' => 'am',
            'ar' => 'ar',
            'hy' => 'hy',
            'rup_MK' => 'rup_MK',
            'frp' => 'frp',
            'as' => 'as',
            'az' => 'az',
            'az_TR' => 'az_TR',
            'bcc' => 'bcc',
            'ba' => 'ba',
            'eu' => 'eu',
            'bel' => 'bel',
            'bn_BD' => 'bn_BD',
            'bs_BA' => 'bs_BA',
            'bre' => 'bre',
            'bg_BG' => 'bg_BG',
            'ca' => 'ca',
            'bal' => 'bal',
            'zh_CN' => 'zh_CN',
            'zh_HK' => 'zh_HK',
            'zh_TW' => 'zh_TW',
            'co' => 'co',
            'hr' => 'hr',
            'cs_CZ' => 'cs_CZ',
            'da_DK' => 'da_DK',
            'dv' => 'dv',
            'nl_NL' => 'nl_NL',
            'nl_BE' => 'nl_BE',
            'dzo' => 'dzo',
            'en_US' => 'en_US',
            'en_AU' => 'en_AU',
            'en_CA' => 'en_CA',
            'en_ZA' => 'en_ZA',
            'en_GB' => 'en_GB',
            'eo' => 'eo',
            'et' => 'et',
            'fo' => 'fo',
            'fi' => 'fi',
            'fr_BE' => 'fr_BE',
            'fr_CA' => 'fr_CA',
            'fr_FR' => 'fr_FR',
            'fy' => 'fy',
            'fuc' => 'fuc',
            'gl_ES' => 'gl_ES',
            'ka_GE' => 'ka_GE',
            'de_DE' => 'de_DE',
            'de_CH' => 'de_CH',
            'el' => 'el',
            'gn' => 'gn',
            'gu' => 'gu',
            'haw_US' => 'haw_US',
            'haz' => 'haz',
            'he_IL' => 'he_IL',
            'hi_IN' => 'hi_IN',
            'hu_HU' => 'hu_HU',
            'is_IS' => 'is_IS',
            'ido' => 'ido',
            'id_ID' => 'id_ID',
            'ga' => 'ga',
            'it_IT' => 'it_IT',
            'ja' => 'ja',
            'jv_ID' => 'jv_ID',
            'kab' => 'kab',
            'kn' => 'kn',
            'kk' => 'kk',
            'km' => 'km',
            'kin' => 'kin',
            'ky_KY' => 'ky_KY',
            'ko_KR' => 'ko_KR',
            'ckb' => 'ckb',
            'lo' => 'lo',
            'lv' => 'lv',
            'li' => 'li',
            'lin' => 'lin',
            'lt_LT' => 'lt_LT',
            'lb_LU' => 'lb_LU',
            'mk_MK' => 'mk_MK',
            'mg_MG' => 'mg_MG',
            'ms_MY' => 'ms_MY',
            'ml_IN' => 'ml_IN',
            'mri' => 'mri',
            'mr' => 'mr',
            'xmf' => 'xmf',
            'mn' => 'mn',
            'me_ME' => 'me_ME',
            'my_MM' => 'my_MM',
            'ne_NP' => 'ne_NP',
            'nb_NO' => 'nb_NO',
            'nn_NO' => 'nn_NO',
            'oci' => 'oci',
            'ory' => 'ory',
            'os' => 'os',
            'ps' => 'ps',
            'fa_IR' => 'fa_IR',
            'fa_AF' => 'fa_AF',
            'pl_PL' => 'pl_PL',
            'pt_BR' => 'pt_BR',
            'pt_PT' => 'pt_PT',
            'pa_IN' => 'pa_IN',
            'rhg' => 'rhg',
            'ro_RO' => 'ro_RO',
            'roh' => 'roh',
            'ru_RU' => 'ru_RU',
            'ru_UA' => 'ru_UA',
            'rue' => 'rue',
            'sah' => 'sah',
            'sa_IN' => 'sa_IN',
            'srd' => 'srd',
            'gd' => 'gd',
            'sr_RS' => 'sr_RS',
            'szl' => 'szl',
            'sd_PK' => 'sd_PK',
            'si_LK' => 'si_LK',
            'sk_SK' => 'sk_SK',
            'sl_SI' => 'sl_SI',
            'so_SO' => 'so_SO',
            'azb' => 'azb',
            'es_AR' => 'es_AR',
            'es_CL' => 'es_CL',
            'es_CO' => 'es_CO',
            'es_MX' => 'es_MX',
            'es_PE' => 'es_PE',
            'es_PR' => 'es_PR',
            'es_ES' => 'es_ES',
            'es_VE' => 'es_VE',
            'su_ID' => 'su_ID',
            'sw' => 'sw',
            'sv_SE' => 'sv_SE',
            'gsw' => 'gsw',
            'tl' => 'tl',
            'tg' => 'tg',
            'tzm' => 'tzm',
            'ta_IN' => 'ta_IN',
            'ta_LK' => 'ta_LK',
            'tt_RU' => 'tt_RU',
            'te' => 'te',
            'th' => 'th',
            'bo' => 'bo',
            'tir' => 'tir',
            'tr_TR' => 'tr_TR',
            'tuk' => 'tuk',
            'ug_CN' => 'ug_CN',
            'uk' => 'uk',
            'ur' => 'ur',
            'uz_UZ' => 'uz_UZ',
            'vi' => 'vi',
            'wa' => 'wa',
            'cy' => 'cy',
        );
    }

}
