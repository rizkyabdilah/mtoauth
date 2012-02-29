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


$mtoauth = new MTOauth($config['client_id'],
                           $config['client_secret'],
                           $config['redirect_uri']);
