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

// get current user info
$exec = $mtoauth->my->info();
if ($exec->http_code != '200'){
    exit('error mindtalk API response :' . $exec->http_info);
}
$result = json_decode($exec->response);
$user = $result->result;

// get home stream timeline
$exec = $mtoauth->my->stream();
if ($exec->http_code != '200'){
    exit('error mindtalk API response :' . $exec->http_info);
}
$result = json_decode($exec->response);
$home_streams = $result->result->posts;
// get user stream
$exec = $mtoauth->user->stream($user->name);
if ($exec->http_code != '200'){
    exit('error mindtalk API response :' . $exec->http_info);
}
$result = json_decode($exec->response);
$streams = $result->result->posts;

// get user supporter, limit 20
$exec = $mtoauth->my->supporter(array('limit' => 20));
if ($exec->http_code != '200'){
    print_r($exec);
    exit('error mindtalk API response :' . $exec->http_info);
}
$result = json_decode($exec->response);
$supporters = $result->result;
?>
<html>
    <head>
        <title>MindTalk Test Client - <?php echo $user->name; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
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
        <h3>Your home stream timeline</h3>
        <ul>
            <?php
            foreach ($home_streams as $i => $val){
            ?>
                <li>
                    <?php echo $val->message; ?><br />
                    <?php
                    foreach ($val->attachments as $j => $valx){
                        if ($valx->kind == 'EmbedPic')
                            print '<img src="' . $valx->medium . '" alt="' . $valx->medium . '" />';
                        elseif ($valx->kind == 'EmbedVideoLink')
                            print $valx->html;
                        else
                            print $valx->url;
                    }
                    ?>
                </li>
            <?php
            }
            ?>
        </ul>
        <h3>Your stream</h3>
        <ul>
            <?php
            foreach ($streams as $i => $val){
            ?>
                <li>
                    <?php echo $val->message; ?><br />
                    <?php
                    foreach ($val->attachments as $j => $valx){
                        if ($valx->kind == 'EmbedPic')
                            print '<img src="' . $valx->medium . '" alt="' . $valx->medium . '" />';
                        elseif ($valx->kind == 'EmbedVideoLink')
                            print $valx->html;
                        else
                            print $valx->url;
                    }
                    ?>
                </li>
            <?php
            }
            ?>
        </ul>
        <h3>Your supporter</h3>
        <ul>
            <?php
            foreach ($supporters as $i => $val){
            ?>
                <li><?php echo $val->name; ?></li>
            <?php
            }
            ?>
        </ul>
        <p><a href="logout.php">Logout</a>
    </body>
</html>
