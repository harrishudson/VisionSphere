<?php

 include 'common.php';

 if (! active_session()) {
  $return_url = 'photolimit.php';
  $return_url_hex = urlencode($return_url);
  header('Location: login.php?RETURN_URL='.$return_url_hex);
  exit();
 }; 

 page_top();

 echo "<h2>Reset Photo Limit</h2>";

 function validate_params() {
  $subscribers = get_subscribers();
  $email = strtolower($_POST['EMAIL_ADDRESS']);
  if(($email == "") or (is_null($email))) {
   echo "<p>Sorry - No Email Address Provided</p>";
   return false;
  }
  if ((substr_count($email,',') > 0) ||
      (substr_count($email,'@') != 1) ||
      (substr_count($email,'.') < 1) ||
      (substr_count($email,' ') > 0)) {
   echo "<p>Sorry - Invalid Email Address</p>";
   return false;
  }
  foreach ($subscribers as $subscriber) {
   if ($subscriber[0] == $email) {
    return true;
   }
  }
  echo "<p>Sorry - Unknown Subscriber</p>";
  return false;
 }

 function update_subscriberss($email) {
  $file_path = './conf/subscribers.txt';
  $file_exists = file_exists($file_path);
  if (!$file_exists) {
   file_put_contents($file_path, null);
  };
  if (($handle = fopen($file_path, 'r+')) !== false) {
   if (flock($handle, LOCK_EX)) {
    $data = [];
    while (($row = fgetcsv($handle)) !== false) {
     $data[] = $row;
    }
    $out_data = [];
    array_push(
     $out_data,
     [ $email,
       null,
       null,
       null ]);
    foreach ($data as $row) {
     if ($row[0] != $email) {
      array_push($out_data, $row);
     }
    };
    rewind($handle);
    ftruncate($handle, 0);
    foreach ($out_data as $row) {
     fputcsv($handle, $row);
    };
    flock($handle, LOCK_UN);
   } else {
    echo "<p>Could not lock the subscribers file for writing</p>";
    return false;
   };
   fclose($handle);
  } else {
   echo "<p>Could not open the subscribers file</p>";
   return false;
  };
  return true;
 };

 $subscribers = get_subscribers();
 if (validate_params()) {
  $email = $_POST['EMAIL_ADDRESS'];
  $result = update_subscriberss($email);
  if ($result) {
   $email_literal = escHTML($email);
   echo "<p>Success - User {$email_literal} Photo Limit Has Been Reset</p>";
  };
 };

 page_bottom();
?>
