<?php

 include 'common.php';

 $config = parse_ini_file('./conf/config.ini', true);
 $max_emails_per_day = $config['EMAIL']['MAX_SUBSCRIBER_EMAILS_PER_DAY'];

 function check_any_subscribers($subscribers) {
  $today = date('Ymd');
  foreach($subscribers as $subscriber) {
   if (($subscriber[1] == $today)) {
    if ((int)$subscriber[2] < $GLOBALS['max_emails_per_day']) {
     return true;
    };
   } else { 
    return true;
   };
  }
  return false; 
 };

 header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
 header("Cache-Control: post-check=0, pre-check=0", false);
 header("Pragma: no-cache");

 $myObj = json_decode('{}');

 if (check_auth($_POST['AUTH_KEY'])) {
  $subscribers = get_subscribers();
  if (check_any_subscribers($subscribers)) {
   $myObj->subscribers = "yes";
   $myObj->error = "";
   $myJson = json_encode($myObj);
   echo $myJson;
  } else {
   $myObj->subscribers = "no";
   $myObj->error = "";
   $myJson = json_encode($myObj);
   echo $myJson;
  }
 } else {
  sleep(1);
  $myObj->error = "Authentication failed";
  $myJson = json_encode($myObj);
  echo $myJson;
 };
 
?>
