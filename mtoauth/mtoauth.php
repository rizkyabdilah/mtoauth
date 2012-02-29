<?php

class StringUtils{
    function startsWith($haystack, $needle, $case=true) {
        if($case)
            return strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0;
        return strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0;
    }
}

class Http{
    
    private $useragent = 'cURL MTOAuth 1.0';
    private $default_header = array('Expect:', 'Pragma: no-cache');
    public $http_code;
    public $http_info;
    public $url;
    public $data;
    public $header;
    
    function __construct($method, $url, $data = array(), $header = array()){
        $this->url = $url;
        $this->data = $data;
        $this->header = $header;
        $this->method = strtoupper($method);
        return $this;
    }
    
    static function parseQS($qs){
        $dict = array();
        $pairs = explode('&', $qs);
        foreach ($pairs as $pair){
            $xpair = explode('=', $pair, 2);
            $dict[$xpair[0]] = urldecode($xpair[1]);
        }
        return $dict;
    }
    
    function execute(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($this->default_header,
                                                         $this->header));
    
        if ($this->method == 'GET'){
            $this->url .= '?' . http_build_query($this->data);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            if (count($this->data)) {
                 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
            }
        }
    
        curl_setopt($ch, CURLOPT_URL, $this->url);
        $response = curl_exec($ch);
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_info = curl_getinfo($ch);
        curl_close ($ch);
        
        return $response;
    }
}

class MTAPI{
    public $api_host = 'http://api.mindtalk.com/v1';
    public $rf = 'json';
    
    protected $access_token;
    protected $refresh_token;
    
    function __construct($access_token, $refresh_token){
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }
}

class MTUser extends MTAPI{
    
    function info($name){
        $conn = new Http('GET', $this->api_host . '/user/info', array('name' => $name, 'rf' => $this->rf)); 
        return $conn->execute();
    }
    
}

class MTMy extends MTAPI{
    
    function info(){
        $conn = new Http('GET', $this->api_host . '/my/info', array('access_token' => $this->access_token, 'rf' => $this->rf)); 
        return $conn->execute();
    }
    
    // todo add wrapper for class authentic, anon api
}

class MTPost extends MTAPI{
    
    function write_mind($message, $origin_id){
        $data = array(
            'message' => $message,
            'origin_id' => $origin_id,
            'access_token' => $this->access_token,
            'rf' => $this->rf
        );
        
        $conn = new Http('POST', $this->api_host . '/post/write_mind', $data); 
        return $conn->execute();
    }
}

class MTOauth{
    
    // constant variable
    public $auth_host = 'http://auth.mindtalk.com';
    
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    
    // wrapper function
    public $user;
    public $my;
    public $post;
    
    function __construct($client_id, $client_secret, $redirect_uri){
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
    }
    
    function authorizeURL(){
        return $this->auth_host . '/authorize';
    }
    
    function accessTokenURL(){
        return $this->auth_host . '/access_token';
    }
    
    function refreshTokenURL(){
        return $this->auth_host . '/refresh_access_token';
    }
    
    function getAuthorizeURL(){
        
        $qs = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri
        );
        
        $auth_url = $this->authorizeURL() . '?' . http_build_query($qs);
        
        return $auth_url;
    }
    
    function getAccessToken($code){
        $data = array(
            'code' => $code,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri
        );
        $conn = new Http('GET', $this->accessTokenURL(), $data);
        $response = $conn->execute();
        $token = Http::parseQS($response);
        return $token;
    }
    
    function refreshToken($refresh_code){
        $data = array(
            'refresh_code' => $refresh_code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri
        );
        $conn = new Http('GET', $this->refreshTokenURL(), $data);
        $response = $conn->execute();
        $new_token = Http::parseQS($response);
        return $new_token;
    }
    
    function setToken($access_token, $refresh_token){
        $this->user = new MTUser($access_token, $refresh_token);
        $this->my = new MTMy($access_token, $refresh_token);
        $this->post = new MTPost($access_token, $refresh_token);
    }
    
}
