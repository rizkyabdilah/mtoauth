<?php
include_once('inc.php');
if ($_SESSION['is_auth'] != 'true'){
    echo 'You are not logged in yet.<br /><br />';
    echo '<a href="redirect.php">Login with MindTalk Account</a>';
    exit;
}

$access_token = $_SESSION['access_token'];
$refresh_token = $_SESSION['refresh_token'];
$mtoauth->setToken($access_token, $refresh_token);

$exec = $mtoauth->my->info();
if ($exec->http_code != '200'){
    exit('error mindtalk API response :' . $exec->http_info);
}
$result = json_decode($exec->response);
$user = $result->result;

$exec = $mtoauth->user->stream($user->name);
if ($exec->http_code != '200'){
    exit('error mindtalk API response :' . $exec->http_info);
}
$result = json_decode($exec->response);
$streams = $result->result->posts;

?>
<html>
    <head>
        <title>MindTalk Test Client - <?php echo $user->name; ?><</title>
        <style type="text/css">
        label{
            display: block;
        }
        </style>
    </head>
    <body>
        <h2>Welcome <?php echo $user->name; ?></h2>
        <p>
            <?php
            if ($_GET['success'] == '1'){
                echo 'Success create mind view ';
                echo 'in <a href="http://www.mindtalk.com/helpers/postView/'.$_GET['post_id'].'">MindTalk</a>';
            } else {
                echo 'In here you can create mind...';
            }
            ?>
            
        </p>
        <p>
            <form action="action.php" method="post" enctype="multipart/form-data">
                <label>Text</label>
                <textarea name="message"></textarea>
                <label>Picture</label>
                <input type="file" name="attach_pic" />
                
                <input type="hidden" name="action" value="post_stream" />
                <input type="hidden" name="origin_id" value="<?php echo $user->id; ?>" />
                <input type="submit" value="Send" />
            </form>
        </p>
        <h3>Your stream</h3>
        <ul>
            <?php
            foreach ($streams as $i => $val){
            ?>
                <li><?php echo $val->message; ?></li>
            <?php
            }
            ?>
        </ul>
        <p><a href="logout.php">Logout</a>
    </body>
</html>
