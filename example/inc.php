<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL ^ E_NOTICE);

include_once('../mtoauth/mtoauth.php');
session_start();

$config = array();
// create your apps in https://auth.mindtalk.com/ui/client/create
$config['client_id'] = 'YOUR-CLIENT-ID';
$config['client_secret'] = 'YOUR-CLIENT-SECRET';
$config['redirect_uri'] = 'YOUR-CALLBACK-URL';
$config['api_key'] = 'YOUR-API-KEY';
$config['scopes'] = 'LIST-SCOPES-SEPARATED-BY-COMMA'; // http://developer.mindtalk.com/api/wiki/AuthScope

$mtoauth = new MTOauth($config['client_id'], $config['client_secret'],
                        $config['redirect_uri'], $config['api_key'], $config['scopes']);
