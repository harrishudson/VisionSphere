<?php 

 include 'common.php';

 page_top();

 echo <<<EOF
  <h2>Authenticate Key / Log In</h2>
EOF;

 $last_timestamp = $_SESSION['auth_timestamp'];
 if (isset($last_timestamp)) {
  $last_timestamp_esc = escHTML(date("Y-m-d H:i:s", $last_timestamp));
  $timeout_mins = escHTML(round($GLOBALS['session_timeout']/60));
  echo <<<EOF
  <p>
   <em>Last Login: {$last_timestamp_esc}. <br/> (Sessions will timeout after; {$timeout_mins} mins).</em>
  </p>
EOF;
 };

 $msg = $_GET['MSG'];
 if ($msg) {
  $msg_esc = escHTML($msg);
  echo <<<EOF
 <p>
  <b>{$msg_esc}</b>
 </p>
EOF;
 };

 echo <<<EOF
 <form action="do_login.php" method="POST">
  <dl>
   <dt>Auth Key *</dt>
   <dd><input type="password" name="AUTH_KEY" placeholder="Auth Key"></dd>
  </dl>
  <input type="hidden" name="RETURN_URL" value="{$_GET['RETURN_URL']}">
  <input id="submit" type="submit" value="Login">
 </form>
EOF;

 page_bottom();
?>
