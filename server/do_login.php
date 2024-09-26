<?php

 include 'common.php';

 $auth_key = $_POST['AUTH_KEY'];
 $return_url = $_POST['RETURN_URL'];
 if ((! isset($return_url) || $return_url == '')) {
  $return_url = get_home();
 };

 if (check_auth($auth_key)) {
  logon();
  header("Location: ".$return_url);
  exit;
 };

 logoff();
 sleep(1);
 $msg = urlencode("Authentication Failed");
 $return_url_esc = urlencode($return_url);
 header("Location: login.php?MSG=".$msg."&RETURN_URL=".$return_url_esc);

?>
