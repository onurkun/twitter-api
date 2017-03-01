<?php
include("SendRequest.php");

class TwitterOauth
{
    private $consumer_key, $consumer_secret, $url;

    public function __construct($consumer_key, $consumer_secret)
    {

        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    /*
    $input = array(
    "username" => ,
    "password" => ,
    "consumer_key" => ,
    "consumer_secret" => ,
    )
    */

    public function getAccessToken($input)
    {

        $ver = (object)$this->tokenURLs($input);
        $curl = new SendRequest($ver->url);
        $curl->setHeader(array($ver->http_header, "Content-Length:", "Expect:"));
        $curl->setPost($ver->params);
        $curl->setHeaderOut(TRUE);
        return $curl->start_single();
    }

    private function tokenURLs($params)
    {
        $array["oauth_callback"] = "oob";
        $array["oauth_consumer_key"] = $params["consumer_key"];
        $array["oauth_nonce"] = md5(uniqid(mt_rand()));
        $array["oauth_signature_method"] = "HMAC-SHA1";
        $array["oauth_timestamp"] = time();
        $array["oauth_token"] = NULL;
        $array["oauth_verifier"] = NULL;
        $array["oauth_version"] = "1.0";
        $param["x_auth_mode"] = "client_auth";
        $param["x_auth_username"] = $params["username"];
        $param["x_auth_password"] = $params["password"];
        $url = "https://api.twitter.com/oauth/access_token";
        $signature_array = $array;
        if (isset($param)) {
            foreach ($param as $key => $val) {
                $signature_array[$key] = $val;
            }
            ksort($signature_array);
        }
        $signature_base_string = "";
        foreach ($signature_array as $key => $val) {
            $signature_base_string .= $key . "=" . rawurlencode($val) . "&";
        }
        $signature_base_string = substr($signature_base_string, 0, -1);
        $signature_base_string = "POST&" . rawurlencode($url) . "&" . rawurlencode($signature_base_string);
        $signing_key = rawurlencode($params["consumer_secret"]) . "&";
        $array["oauth_signature"] = base64_encode(hash_hmac("sha1", $signature_base_string, $signing_key, true));
        $http_header = "Authorization:OAuth ";
        foreach ($array as $key => $val) {
            $http_header .= $key . "=\"" . rawurlencode($val) . "\",";
        }
        $http_header = substr($http_header, 0, -1);

        if (isset($param)) {
            $url .= "?" . http_build_query($param);
        }
        return array("http_header" => $http_header, "url" => $url, "params" => $param);
    }

}