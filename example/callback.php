<?php
include_once('inc.php');

$token = $mtoauth->getAccessToken($_GET['code']);

$_SESSION['access_token'] = $token['access_token'];
$_SESSION['refresh_token'] = $token['refresh_token'];
$_SESSION['track_id'] = $token['track_id'];
$_SESSION['is_auth'] = 'true';

header('Location: ./index.php');
