<?php
include_once('inc.php');

header('Location: ' . $mtoauth->getAuthorizeURL());
exit;
