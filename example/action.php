<?php
include_once('inc.php');

$access_token = $_SESSION['access_token'];
$refresh_token = $_SESSION['refresh_token'];
$mtoauth->setToken($access_token, $refresh_token);

if ($_POST['action'] == 'post_stream'){
    $message = $_POST['message'];
    $origin_id = $_POST['origin_id'];
    
    // attach picture if user upload a file, reject if not an image
    $upload_file = false;
    if (isset($_FILES['attach_pic']) && !empty($_FILES['attach_pic']['name'])){
        if (substr($_FILES['attach_pic']['type'], 0, 5) != 'image'){
            exit('file type not allowed!!');
        }
        
        $file_path = '/tmp/' . $_FILES['attach_pic']['name'];
        if (move_uploaded_file($_FILES['attach_pic']['tmp_name'], $file_path)){
            $upload_file = $file_path;
        }
    }
    
    $exec = $mtoauth->post->write_mind($message, $origin_id, $upload_file);
    $response = json_decode($exec->response);
    $post = $response->result;
    // delete file after upload
    if ($upload_file){
        unlink($upload_file);
    }
}

header('Location: ./index.php?success=1&post_id='.$post->id);
