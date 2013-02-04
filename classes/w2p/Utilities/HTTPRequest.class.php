<?php

/**
 * web2Project implementation of an HTTP handler
 *
 * @package     web2project\utilities
 */

class w2p_Utilities_HTTPRequest {
    private $url = '';
    private $urlParams = array();

    public function __construct($url = '')
    {
        $this->url = $url;
    }

    public function addParameters($urlParams)
    {
        $this->urlParams = $urlParams;
    }

    public function processRequest()
    {
        $params = http_build_query($this->urlParams);

        if (function_exists('curl_init')) {
            $ch = curl_init($this->url);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec( $ch );
            curl_close ($ch);
        } else {
            /*
             * Thanks to Wez Furlong for the core of the logic for this
             *   method to POST data via PHP without cURL
             *   http://netevil.org/blog/2006/nov/http-post-from-php-without-curl
             */
            $ctx = stream_context_create($params);
            $fp = @fopen($this->url, 'rb', false, $ctx);
            if (!$fp) {
                throw new Exception("Problem with $url, $php_errormsg");
            }
            $response = @stream_get_contents($fp);
            if ($response === false) {
                throw new Exception("Problem reading data from $url, $php_errormsg");
            }
        }
        return $response;
    }
}