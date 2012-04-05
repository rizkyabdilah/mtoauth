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
    private $timeout = 5;
    private $connect_timeout = 1;
    public $url;
    public $param;
    public $header;
    public $http_code;
    public $http_info;
    public $response;
    
    function __construct($method, $url, $param = array(), $header = array()){
        $this->url = $url;
        $this->param = $param;
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($this->default_header,
                                                         $this->header));
    
        if ($this->method == 'GET'){
            $this->url .= '?' . http_build_query($this->param);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            if (count($this->param)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->param);
            }
        }
    
        curl_setopt($ch, CURLOPT_URL, $this->url);
        $this->response = curl_exec($ch);
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_info = curl_getinfo($ch);
        curl_close ($ch);
        
        return $this;
    }
}

class MTAPI{
    public $api_host = 'http://api.mindtalk.com/v1';
    public $rf = 'json';
    public $api_key;
    
    protected $access_token;
    protected $refresh_token;
    
    public $authentic = false;
    
    function __construct($api_key, $access_token, $refresh_token){
        $this->api_key = $api_key;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }
    
    function getApiHost($path = ''){
        return $this->api_host . $path;
    }
    
    function getRequest($endpoint, $params=array(), $authentic=false){
        $params['rf'] = $this->rf;
        if ($this->authentic || $authentic)
            $params['access_token'] = $this->access_token;
        $req = new Http('GET', $this->getApiHost($endpoint), $params);
        return $req;
    }
    
    function postRequest($endpoint, $params=array(), $authentic=false){
        $params['rf'] = $this->rf;
        if ($this->authentic || $authentic)
            $params['access_token'] = $this->access_token;
        else
            $params['api_key'] = $this->api_key;
        $req = new Http('POST', $this->getApiHost($endpoint), $params);
        return $req;
    }
    
    // todo add method for set return format
}

class MTUser extends MTAPI{
    
    function info($name){
        $conn = $this->getRequest('/user/info', array('name' => $name));
        return $conn->execute();
    }
    
    function supporters($name, $params=array()){
        $params['name'] = $name;
        $conn = $this->getRequest('/user/supporters', $params);
        return $conn->execute();
    }
    
    function supporting($name, $params=array()){
        $params['name'] = $name;
        $conn = $this->getRequest('/user/supporting', $params);
        return $conn->execute();
    }
    
    function search($query=array()){
        $conn = $this->getRequest('/user/search', $query);
        return $conn->execute();
    }
    
    function channels($name, $params=array()){
        $params['name'] = $name;
        $conn = $this->getRequest('/user/channels', $params);
        return $conn->execute();
    }
    
    function newest($params=array()){
        $conn = $this->getRequest('/user/newest', $params);
        return $conn->execute();
    }
    
    function is_support($s_user_id, $t_user_id){
        $params = array('s_user_id' => $s_user_id, 't_user_id' => $t_user_id);
        $conn = $this->getRequest('/user/is_support', $params);
        return $conn->execute();
    }
    
    function trophies($name){
        $conn = $this->getRequest('/user/trophies', array('name' => $name));
        return $conn->execute();
    }
    
    function stream($name, $params=array()){
        $params['name'] = $name;
        $conn = $this->getRequest('/user/stream', $params);
        return $conn->execute();
    }
    
    function update_profile($params=array()){
        $conn = $this->postRequest('/user/update_profile', $params, true);
        return $conn->execute();
    }
    
    function support($uidname){
        $params = array('uidname' => $uidname);
        $conn = $this->postRequest('/user/support', $params, true);
        return $conn->execute();
    }
    
    function unsupport($uidname){
        $params = array('uidname' => $uidname);
        $conn = $this->postRequest('/user/unsupport', $params, true);
        return $conn->execute();
    }
    
}

class MYIam extends MTAPI{
    
    function __construct($api_key, $access_token, $refresh_token){
        parent::__construct($api_key, $access_token, $refresh_token);
        $this->authentic = true;
    }
    
    function supporting($params=array()){
        $conn = $this->getRequest('/my/supporting', $params);
        return $conn->execute();
    }
}

class MTMy extends MTAPI{
    
    function __construct($api_key, $access_token, $refresh_token){
        parent::__construct($api_key, $access_token, $refresh_token);
        $this->authentic = true;
    }
    
    function info(){
        $conn = $this->postRequest('/my/info'); 
        return $conn->execute();
    }
    
    function supporter($params=array()){
        $conn = $this->getRequest('/my/supporter', $params);
        return $conn->execute();
    }
    
    function stream($params=array()){
        $conn = $this->getRequest('/my/stream', $params);
        return $conn->execute();
    }
    
    function email(){
        $conn = $this->getRequest('/my/email');
        return $conn->execute();
    }
    
    function birth_date(){
        $conn = $this->getRequest('/my/birth_date');
        return $conn->execute();
    }
    
    function channels($params=array()){
        $conn = $this->getRequest('/my/info', $params);
        return $conn->execute();
    }
    
    function notifications($params=array()){
        $conn = $this->getRequest('/my/notifications', $params);
        return $conn->execute();
    }
}

class MTPost extends MTAPI{
    
    function write_mind($message, $origin_id, $attach_pic=false, $autopost_fb=0, $autopost_tw=0){
        
        $params = array(
            'message' => $message,
            'origin_id' => $origin_id,
            'autopost_fb' => $autopost_fb,
            'autopost_tw' => $autopost_tw,
            'access_token' => $this->access_token,
            'rf' => $this->rf
        );
        
        if ($attach_pic){
            if (!StringUtils::startsWith($attach_pic, '@')){
                $attach_pic = sprintf('%s%s', '@', $attach_pic);
            }
            $params['attach_pic'] = $attach_pic;
        }
        
        $conn = new Http('POST', $this->getApiHost('/post/write_mind'), $params); 
        return $conn->execute();
    }
}

class MTOauth{
    
    // constant variable
    public $auth_host = 'http://auth.mindtalk.com';
    
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $api_key;
    
    // wrapper function
    public $user;
    public $my;
    public $post;
    
    function __construct($client_id, $client_secret, $redirect_uri, $api_key){
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->api_key = $api_key;
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
        $exec = $conn->execute();
        $token = Http::parseQS($exec->response);
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
        $exec = $conn->execute();
        $new_token = Http::parseQS($exec->response);
        return $new_token;
    }
    
    function setToken($access_token, $refresh_token){
        $this->user = new MTUser($this->api_key, $access_token, $refresh_token);
        $this->my = new MTMy($this->api_key, $access_token, $refresh_token);
        $this->post = new MTPost($this->api_key, $access_token, $refresh_token);
    }
    
}
