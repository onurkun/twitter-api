<?php

class SendRequest
{
    private $options = array();
    private $allow_header = TRUE;

    // json_encode(array("url" => "https://google.com"));

    public function __construct($url)
    {
        $this->setFirstSettings($url);
    }

    public function start_single()
    {
        $headers = array();
        $ch = isset($ch) ? $ch : curl_init();
        $header_function = function ($ch, $header) use (&$headers) {
            $key = (string)$ch;
            $_header = trim($header);
            if (strpos($_header, ':') != FALSE) {
                list($name, $val) = explode(':', $_header, 2);
                $name = strtolower($name);
                $val = ltrim(rtrim($val));
                if (isset($headers[$key][$name])) {
                    $headers[$key][$name] .= PHP_EOL . $val;
                } else {
                    $headers[$key][$name] = $val;
                }
            }
            return strlen($header);
        };
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, $header_function);
        $content = curl_exec($ch);
        $channel_key = (string)$ch;
        $headers = @$headers[$channel_key];
        $obj = new stdClass();
        $obj->http_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $obj->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $obj->http_cookie = @$headers["set-cookie"];
        $obj->is_json = $this->isJson($content) ? TRUE : FALSE;
        if (isset($this->options[CURLINFO_HEADER_OUT]) && $this->options[CURLINFO_HEADER_OUT]) {
            $ret = curl_getinfo($ch, CURLINFO_HEADER_OUT);
            list($_headers, $_content) = explode("\r\n\r\n", $ret, 2);
            $obj->headers_out = explode("\n", str_replace("\r", "", $_headers));
            unset($_headers, $_content, $ret);
        }
        if (isset($this->allow_header) && $this->allow_header) {
            $obj->headers_in = $headers;
        }
        $obj->content = $content;
        if ($obj->is_json == TRUE) {
            $obj = (object)array_merge((array)$obj, json_decode($content, TRUE));
        }
        return $obj;
    }

    private function setFirstSettings($url)
    {
        $this->options = array(
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array(),
            CURLOPT_COOKIESESSION => FALSE,
            CURLINFO_HEADER_OUT => FALSE,
        );
    }

    /*
        $this->setPost("xxx" => "yyy");
     */

    public function setPost($input)
    {
        if (is_array($input) && count($input) > 0) {
            $this->options[CURLOPT_POST] = true;
            $this->options[CURLOPT_HTTPGET] = false;
            $this->options[CURLOPT_POSTFIELDS] = http_build_query($input, '', '&');
        }
    }

    public function setHeader($input)
    {
        $this->options[CURLOPT_HTTPHEADER] = $input;
    }

    public function setUserAgent($input)
    {
        $this->options[CURLOPT_USERAGENT] = $input;
    }

    public function setCookie($input)
    {
        $this->options[CURLOPT_COOKIE] = $input;
    }

     public function setInterface($input)
    {
        $this->options[CURLOPT_INTERFACE] = $input;
    }

    public function setHeaderOut($input)
    {
        if (is_bool($input)) {
            $this->options[CURLINFO_HEADER_OUT] = $input;
        }
    }

    public function setHeaderIN($input)
    {
        if (is_bool($input)) {
            $this->allow_header = $input;
        }
    }

    /*
     * $this->setProxy("127.0.01:8888")
    */

    public function setProxy($input)
    {
        if (strstr($input, ":")) {
            $this->options[CURLOPT_PROXY] = $input;
            return true;
        } else {
            return false;
        }
    }

    function isJson($input)
    {
        json_decode($input);
        return (json_last_error() == JSON_ERROR_NONE);
    }


}