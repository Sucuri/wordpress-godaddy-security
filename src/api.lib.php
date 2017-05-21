<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Plugin API library.
 *
 * When used in the context of web development, an API is typically defined as a
 * set of Hypertext Transfer Protocol (HTTP) request messages, along with a
 * definition of the structure of response messages, which is usually in an
 * Extensible Markup Language (XML) or JavaScript Object Notation (JSON) format.
 * While "web API" historically has been virtually synonymous for web service,
 * the recent trend (so-called Web 2.0) has been moving away from Simple Object
 * Access Protocol (SOAP) based web services and service-oriented architecture
 * (SOA) towards more direct representational state transfer (REST) style web
 * resources and resource-oriented architecture (ROA). Part of this trend is
 * related to the Semantic Web movement toward Resource Description Framework
 * (RDF), a concept to promote web-based ontology engineering technologies. Web
 * APIs allow the combination of multiple APIs into new applications known as
 * mashups.
 *
 * @see https://en.wikipedia.org/wiki/Application_programming_interface#Web_APIs
 */
class GddysecAPI extends GddysecOption
{
    /**
     * Check whether the SSL certificates will be verified while executing a HTTP
     * request or not. This is only for customization of the administrator, in fact
     * not verifying the SSL certificates can lead to a "Man in the Middle" attack.
     *
     * @return boolean Whether the SSL certs will be verified while sending a request.
     */
    public static function verifySslCert()
    {
        return (self::get_option(':verify_ssl_cert') === 'true');
    }

    /**
     * Seconds before consider a HTTP request as timeout.
     *
     * As for the 01/Jan/2016 if the number of seconds before a timeout is greater
     * than sixty (which is one minute) the function will reset the option to its
     * default value to keep the latency of the HTTP requests in a minimum to
     * minimize the interruptions in the admins workflow. The normal connection
     * timeout should be in the range of ten seconds, or fifteen if the DNS lookups
     * are slow.
     *
     * @return integer Seconds to consider a HTTP request timeout.
     */
    public static function requestTimeout()
    {
        $timeout = (int) self::get_option(':request_timeout');

        if ($timeout > GDDYSEC_MAX_REQUEST_TIMEOUT) {
            self::delete_option(':request_timeout');

            return self::requestTimeout();
        }

        return $timeout;
    }

    /**
     * Generate an user-agent for the HTTP requests.
     *
     * @return string An user-agent for the HTTP requests.
     */
    private static function curlUserAgent()
    {
        return sprintf(
            'WordPress/%s; %s',
            self::site_version(),
            self::get_domain()
        );
    }

    /**
     * Alternative to the built-in PHP function http_build_query.
     *
     * Some PHP installations with different encoding or with different language
     * (German for example) might produce an unwanted behavior when building an
     * URL, because of this we decided to write our own URL query builder to
     * keep control of the output.
     *
     * @param  array  $params May be an array or object containing properties.
     * @return string         Returns a URL-encoded string.
     */
    private static function buildQuery($params = array())
    {
        $trail = '';

        foreach ($params as $param => $value) {
            $value = urlencode($value);
            $trail .= sprintf('&%s=%s', $param, $value);
        }

        return substr($trail, 1);
    }

    private static function canCurlFollowRedirection()
    {
        $safe_mode = ini_get('safe_mode');
        $open_basedir = ini_get('open_basedir');

        if ($safe_mode === '1' || $safe_mode === 'On') {
            return false;
        }

        if (!empty($open_basedir)) {
            return false;
        }

        return true;
    }

    /**
     * Communicates with a remote URL and retrieves its content.
     *
     * Curl is a reflective object-oriented programming language for interactive
     * web applications whose goal is to provide a smoother transition between
     * formatting and programming. It makes it possible to embed complex objects
     * in simple documents without needing to switch between programming
     * languages or development platforms.
     *
     * Using Curl instead of the custom WordPress HTTP functions allow us to
     * control the functionality at 100% without expecting breaking changes in
     * newer versions of the code. For exampe, as of WordPress 4.6.x the result
     * of executing the functions prefixed with "wp_remote_" returns an object
     * WP_HTTP_Requests_Response that is not compatible with older implementations
     * of the plugin.
     *
     * @see https://secure.php.net/manual/en/book.curl.php
     *
     * @param  string $url    The target URL where the request will be sent.
     * @param  string $method HTTP method that will be used to send the request.
     * @param  array  $params Parameters for the request defined in an associative array.
     * @param  array  $args   Request arguments like the timeout, headers, cookies, etc.
     * @return array          Response object after the HTTP request is executed.
     */
    public static function apiCall($url = '', $method = 'GET', $params = array(), $args = array())
    {
        if ($url && ($method === 'GET' || $method === 'POST')) {
            $output = self::apiCallCurl($url, $method, $params, $args);
            $result = @json_decode($output, true);

            if ($result) {
                return $result;
            }

            return $output;
        }

        return false;
    }

    private static function apiCallCurl($url = '', $method = 'GET', $params = array(), $args = array())
    {
        if ($url
            && function_exists('curl_init')
            && ($method === 'GET' || $method === 'POST')
        ) {
            $curl = curl_init();
            $timeout = self::requestTimeout();

            if (is_array($args) && isset($args['timeout'])) {
                $timeout = $args['timeout'];
            }

            // Add random request parameter to avoid request reset.
            if (!empty($params) && !array_key_exists('time', $params)) {
                $params['time'] = time();
            }

            if ($method === 'GET'
                && is_array($params)
                && !empty($params)
            ) {
                $url .= '?' . self::buildQuery($params);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, self::curlUserAgent());
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout * 2);

            if (self::canCurlFollowRedirection()) {
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 2);
            }

            if ($method === 'POST') {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, self::buildQuery($params));
            }

            if (self::verifySslCert()) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            } else {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            }

            $output = curl_exec($curl);
            $header = curl_getinfo($curl);
            $errors = curl_error($curl);

            curl_close($curl);

            if (array_key_exists('http_code', $header)
                && $header['http_code'] === 200
                && !empty($output)
            ) {
                return $output;
            }

            Gddysec::throwException($errors);
        }

        return false;
    }

    /**
     * Check whether the plugin API key is valid or not.
     *
     * @param  string  $api_key An unique string to identify this installation.
     * @return boolean          True if the API key is valid, false otherwise.
     */
    private static function isValidKey($api_key = '')
    {
        return (bool) @preg_match('/^[a-z0-9]{32}$/', $api_key);
    }

    /**
     * Store the API key locally.
     *
     * @param  string  $api_key  An unique string of characters to identify this installation.
     * @param  boolean $validate Whether the format of the key should be validated before store it.
     * @return boolean           Either true or false if the key was saved successfully or not respectively.
     */
    public static function setPluginKey($api_key = '', $validate = false)
    {
        if ($validate) {
            if (!self::isValidKey($api_key)) {
                GddysecInterface::error('Invalid API key format');
                return false;
            }
        }

        if (!empty($api_key)) {
            GddysecEvent::notify_event('plugin_change', 'API key updated successfully: ' . $api_key);
        }

        return self::update_option(':api_key', $api_key);
    }

    /**
     * Retrieve the API key from the local storage.
     *
     * @return string|boolean The API key or false if it does not exists.
     */
    public static function getPluginKey()
    {
        $api_key = self::get_option(':api_key');

        if (is_string($api_key)
            && self::isValidKey($api_key)
        ) {
            return $api_key;
        }

        return false;
    }

    /**
     * Call an action from the remote API interface of our WordPress service.
     *
     * @param  string  $method       HTTP method that will be used to send the request.
     * @param  array   $params       Parameters for the request defined in an associative array of key-value.
     * @param  boolean $send_api_key Whether the API key should be added to the request parameters or not.
     * @param  array   $args         Request arguments like the timeout, redirections, headers, cookies, etc.
     * @return array                 Response object after the HTTP request is executed.
     */
    public static function apiCallWordpress($method = 'GET', $params = array(), $send_api_key = true, $args = array())
    {
        $url = GDDYSEC_API;
        $params[ GDDYSEC_API_VERSION ] = 1;
        $params['p'] = 'wordpress';

        if ($send_api_key) {
            $api_key = self::getPluginKey();

            if (!$api_key) {
                return false;
            }

            $params['k'] = $api_key;
        }

        return self::apiCall($url, $method, $params, $args);
    }

    /**
     * Determine whether an API response was successful or not checking the expected
     * generic variables and types, in case of an error a notification will appears
     * in the administrator panel explaining the result of the operation.
     *
     * @param  array   $response HTTP response after API endpoint execution.
     * @param  boolean $enqueue  Add the log to the local queue on a failure.
     * @return boolean           False if the API call failed, true otherwise.
     */
    private static function handleResponse($response = array(), $enqueue = true)
    {
        if ($response !== false) {
            if (is_array($response)
                && array_key_exists('status', $response)
                && intval($response['status']) === 1
            ) {
                return true;
            }

            if (is_array($response)
                && array_key_exists('messages', $response)
                && !empty($response['messages'])
            ) {
                return self::handleErrorResponse($response, $enqueue);
            }
        }

        return false;
    }

    /**
     * Process failures in the HTTP response.
     *
     * Log file not found: means that the API key used to execute the request is
     * not associated to the website, this may indicate that either the key was
     * invalidated by an administrator of the service or that the API key was
     * custom generated with invalid data.
     *
     * Wrong API key: means that the TLD of the origin of the request is not the
     * domain used to generate the API key in the first place, or that the email
     * address of the site administrator was changed so the data is not valid
     * anymore.
     *
     * Connection timeout: means that the API service is down either because the
     * hosting provider has connectivity issues or because the code is being
     * deployed. There is an option in the settings page that allows to temporarily
     * disable the communication with the API service while the server is down, this
     * allows the admins to keep the latency at zero and continue working in their
     * websites without interruptions.
     *
     * SSL issues: depending on the options used to compile the OpenSSL library
     * built by each hosting provider, the connection with the HTTPs version of the
     * API service may be rejected because of a failure in the SSL algorithm check.
     * There is an option in the settings page that allows to disable the SSL pair
     * verification, this option it disable automatically when the error is detected
     * for the first time.
     *
     * @param  array   $response HTTP response after API endpoint execution.
     * @param  boolean $enqueue  Add the log to the local queue on a failure.
     * @return boolean           False if the API call failed, true otherwise.
     */
    private static function handleErrorResponse($response = array(), $enqueue = true)
    {
        $msg = 'Unknown error, there is no more information.';

        if (is_array($response)
            && array_key_exists('messages', $response)
            && !empty($response['messages'])
        ) {
            $msg = implode(".\x20", $response['messages']);
            $raw = $msg; /* Keep a copy of the original message. */

            // Special response for invalid API keys.
            if (stripos($raw, 'log file not found') !== false) {
                $key = GddysecOption::get_option(':api_key');
                $msg .= '; this generally happens when you add an invalid API '
                . 'key, the key will be deleted automatically to hide these w'
                . 'arnings, if you want to recover it go to the settings page'
                . ' and use the recover button to send the key to your email '
                . 'address: ' . Gddysec::escape($key);

                // GddysecOption::delete_option(':api_key');
            }

            // Check if the MX records as missing for API registration.
            if (strpos($raw, 'Invalid email') !== false) {
                $msg = 'Email has an invalid format, or the host '
                . 'associated to the email has no MX records.';
            }
        }

        if (!empty($msg) && $enqueue) {
            GddysecInterface::error($msg);
        }

        return false;
    }

    /**
     * Send a request to the API to register this site.
     *
     * @param  string  $email Optional email address for the registration.
     * @return boolean        True if the API key was generated, false otherwise.
     */
    public static function registerSite($email = '')
    {
        if (!is_string($email) || empty($email)) {
            $email = self::get_site_email();
        }

        $response = self::apiCallWordpress('POST', array(
            'e' => $email,
            's' => self::get_domain(),
            'a' => 'register_site',
        ), false);

        if (self::handleResponse($response)) {
            self::setPluginKey($response['output']['api_key']);

            GddysecEvent::schedule_task();
            GddysecEvent::notify_event('plugin_change', 'Site registered and API key generated');
            GddysecInterface::info('The API key for your site was successfully generated and saved.');

            return true;
        }

        return false;
    }

    /**
     * Send a request to recover a previously registered API key.
     *
     * @return boolean true if the API key was sent to the administrator email, false otherwise.
     */
    public static function recoverKey()
    {
        $clean_domain = self::get_domain();

        $response = self::apiCallWordpress('GET', array(
            'e' => self::get_site_email(),
            's' => $clean_domain,
            'a' => 'recover_key',
        ), false);

        if (self::handleResponse($response)) {
            GddysecEvent::notify_event('plugin_change', 'API key recovered for domain: ' . $clean_domain);
            GddysecInterface::info($response['output']['message']);

            return true;
        }

        return false;
    }

    /**
     * Send a request to the API to store and analyze the events of the site. An
     * event can be anything from a simple request, an internal modification of the
     * settings or files in the administrator panel, or a notification generated by
     * this plugin.
     *
     * @param  string  $event   Event triggered by the core system functions.
     * @param  integer $time    Timestamp when the event was originally triggered.
     * @param  boolean $enqueue Add the log to the local queue on a failure.
     * @return boolean          True if the event was logged, false otherwise.
     */
    public static function sendLog($event = '', $time = 0, $enqueue = true)
    {
        if (!empty($event)) {
            $params = array();
            $params['a'] = 'send_log';
            $params['m'] = $event;

            if (intval($time) > 0) {
                $params['time'] = (int) $time;
            }

            $response = self::apiCallWordpress('POST', $params, true);

            if (self::handleResponse($response, $enqueue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send all logs from the queue.
     *
     * Retry the HTTP calls for the logs that were not sent to the API service
     * because of a connection failure or misconfiguration. Each successful call
     * will remove the log from the queue and the failures will keep them until the
     * next function call is executed.
     *
     * @return void
     */
    public static function sendLogsFromQueue()
    {
        $cache = new GddysecCache('auditqueue');
        $entries = $cache->getAll();

        if (is_array($entries) && !empty($entries)) {
            foreach ($entries as $key => $entry) {
                $result = self::sendLog(
                    $entry->message,
                    $entry->created_at,
                    false
                );

                if ($result === true) {
                    $cache->delete($key);
                } else {
                    /**
                     * Stop loop on failures.
                     *
                     * If the log was successfully sent to the API service then we can continue
                     * sending the other logs in the queue, otherwise the operation must be stopped
                     * so it can be executed next time when the service is online, not stopping the
                     * operation when one or more of the API calls fails will cause a very long
                     * delay in the load of the page that is being requested.
                     */
                    break;
                }
            }
        }
    }

    /**
     * Retrieve the event logs registered by the API service.
     *
     * @param  integer $lines How many lines from the log file will be retrieved.
     * @return string         The response of the API service.
     */
    public static function getAuditLogs($lines = 50)
    {
        $response = self::apiCallWordpress(
            'GET',
            array('a' => 'get_logs', 'l' => $lines),
            true /* send API key with the request */,
            array('timeout' => 20) /* force more time */
        );

        if (self::handleResponse($response)) {
            $response['output_data'] = array();
            $log_pattern = '/^([0-9\-]+) ([0-9:]+) (\S+) : (.+)/';
            $extra_pattern = '/(.+ \(multiple entries\):) (.+)/';
            $generic_pattern = '/^@?([A-Z][a-z]{3,7}): ([^;]+; )?(.+)/';
            $auth_pattern = '/^User authentication (succeeded|failed): ([^<;]+)/';

            foreach ($response['output'] as $log) {
                if (@preg_match($log_pattern, $log, $log_match)) {
                    $log_data = array(
                        'event' => 'notice',
                        'date' => '',
                        'time' => '',
                        'datetime' => '',
                        'timestamp' => 0,
                        'account' => $log_match[3],
                        'username' => 'system',
                        'remote_addr' => '127.0.0.1',
                        'message' => $log_match[4],
                        'file_list' => false,
                        'file_list_count' => 0,
                    );

                    // Extract and fix the date and time using the Eastern time zone.
                    $datetime = sprintf('%s %s EDT', $log_match[1], $log_match[2]);
                    $log_data['timestamp'] = strtotime($datetime);
                    $log_data['datetime'] = date('Y-m-d H:i:s', $log_data['timestamp']);
                    $log_data['date'] = date('Y-m-d', $log_data['timestamp']);
                    $log_data['time'] = date('H:i:s', $log_data['timestamp']);

                    // Extract more information from the generic audit logs.
                    $log_data['message'] = str_replace('<br>', '; ', $log_data['message']);

                    if (@preg_match($generic_pattern, $log_data['message'], $log_extra)) {
                        $log_data['event'] = strtolower($log_extra[1]);
                        $log_data['message'] = trim($log_extra[3]);

                        // Extract the username and remote address from the log.
                        if (!empty($log_extra[2])) {
                            $username_address = rtrim($log_extra[2], ";\x20");

                            // Separate the username from the remote address.
                            if (strpos($username_address, ",\x20") !== false) {
                                $usip_parts = explode(",\x20", $username_address, 2);

                                if (count($usip_parts) == 2) {
                                    // Separate the username from the display name.
                                    $log_data['username'] = @preg_replace('/^.+ \((.+)\)$/', '$1', $usip_parts[0]);
                                    $log_data['remote_addr'] = $usip_parts[1];
                                }
                            } else {
                                $log_data['remote_addr'] = $username_address;
                            }
                        }

                        // Fix old user authentication logs for backward compatibility.
                        $log_data['message'] = str_replace(
                            'logged in',
                            'authentication succeeded',
                            $log_data['message']
                        );

                        if (@preg_match($auth_pattern, $log_data['message'], $user_match)) {
                            $log_data['username'] = $user_match[2];
                        }
                    }

                    // Extract more information from the special formatted logs.
                    if (@preg_match($extra_pattern, $log_data['message'], $log_extra)) {
                        $log_data['message'] = $log_extra[1];
                        $log_extra[2] = str_replace(', new size', '; new size', $log_extra[2]);
                        $log_extra[2] = str_replace(",\x20", ";\x20", $log_extra[2]);
                        $log_data['file_list'] = explode(',', $log_extra[2]);
                        $log_data['file_list_count'] = count($log_data['file_list']);
                    }

                    $response['output_data'][] = $log_data;
                }
            }

            return $response;
        }

        return false;
    }

    /**
     * Parse the event logs with multiple entries.
     *
     * @param  string $event_log Event log that will be processed.
     * @return array             List of parts of the event log.
     */
    public static function parseMultipleEntries($event_log = '')
    {
        if (@preg_match('/^(.*:\s)\(multiple entries\):\s(.+)/', $event_log, $match)) {
            $event_log = array();
            $event_log[] = trim($match[1]);
            $grouped_items = @explode(',', $match[2]);
            $event_log = array_merge($event_log, $grouped_items);
        }

        return $event_log;
    }

    /**
     * Send a request to the API to store and analyze the file's hashes of the site.
     * This will be the core of the monitoring tools and will enhance the
     * information of the audit logs alerting the administrator of suspicious
     * changes in the system.
     *
     * @param  string  $hashes The information gathered after the scanning of the site's files.
     * @return boolean         true if the hashes were stored, false otherwise.
     */
    public static function sendHashes($hashes = '')
    {
        if (!empty($hashes)) {
            $response = self::apiCallWordpress('POST', array(
                'a' => 'send_hashes',
                'h' => $hashes,
            ));

            if (self::handleResponse($response)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scan a website through the public SiteCheck API [1] for known malware,
     * blacklisting status, website errors, and out-of-date software.
     *
     * [1] https://sitecheck.sucuri.net/
     *
     * @param  string  $domain The clean version of the website's domain.
     * @param  boolean $clear  Request the results from a fresh scan or not.
     * @return object          JSON encoded website scan results.
     */
    public static function getSitecheckResults($domain = '', $clear = true)
    {
        if (!empty($domain)) {
            $params = array();
            $timeout = (int) GddysecOption::get_option(':sitecheck_timeout');
            $params['scan'] = $domain;
            $params['fromwp'] = 2;
            $params['json'] = 1;

            // Request a fresh scan or not.
            if ($clear === true) {
                $params['clear'] = 1;
            }

            $response = self::apiCall(
                'https://sitecheck.sucuri.net/',
                'GET',
                $params,
                array(
                    'assoc' => true,
                    'timeout' => $timeout,
                )
            );

            return $response;
        }

        return false;
    }

    /**
     * Retrieve a list with the checksums of the files in a specific version of WordPress.
     *
     * @see Release Archive https://wordpress.org/download/release-archive/
     *
     * @param  integer $version Valid version number of the WordPress project.
     * @return object           Associative object with the relative filepath and the checksums of the project files.
     */
    public static function getOfficialChecksums($version = 0)
    {
        $language = GddysecOption::get_option(':language');
        $response = self::apiCall(
            'https://api.wordpress.org/core/checksums/1.0/',
            'GET',
            array(
                'version' => $version,
                'locale' => $language,
            )
        );

        if (is_array($response)
            && array_key_exists('checksums', $response)
            && !empty($response['checksums'])
        ) {
            if (count((array) $response['checksums']) <= 1
                && array_key_exists($version, $response['checksums'])
            ) {
                return $response['checksums'][$version];
            } else {
                return $response['checksums'];
            }
        }

        return false;
    }

    /**
     * Retrieve a specific file from the official WordPress subversion repository,
     * the content of the file is determined by the tags defined using the site
     * version specified. Only official core files are allowed to fetch.
     *
     * @see https://core.svn.wordpress.org/
     * @see https://i18n.svn.wordpress.org/
     * @see https://core.svn.wordpress.org/tags/VERSION_NUMBER/
     *
     * @param  string $filepath Relative file path of a project core file.
     * @param  string $version  Optional site version, default will be the global version number.
     * @return string           Full content of the official file retrieved, false if the file was not found.
     */
    public static function getOriginalCoreFile($filepath = '', $version = 0)
    {
        if (!empty($filepath)) {
            if ($version == 0) {
                $version = self::site_version();
            }

            $url = sprintf('https://core.svn.wordpress.org/tags/%s/%s', $version, $filepath);
            $response = self::apiCall($url, 'GET');

            if ($response) {
                return $response;
            }
        }

        return false;
    }
}
