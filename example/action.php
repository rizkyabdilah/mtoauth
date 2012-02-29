<?php
include_once('inc.php');

$access_token = $_SESSION['access_token'];
$refresh_token = $_SESSION['refresh_token'];
$mtoauth->setToken($access_token, $refresh_token);

if ($_POST['action'] == 'post_stream'){
    $message = $_POST['message'];
    $origin_id = $_POST['origin_id'];
    $result = json_decode($mtoauth->post->write_mind($message, $origin_id));
    $post = $result->result;
}

header('Location: ./index.php?success=1&post_id='.$post->id);
