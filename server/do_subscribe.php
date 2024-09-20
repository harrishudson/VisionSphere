<?php

 include 'common.php';

 if (! active_session()) {
  $return_url = get_home();
  $return_url_hex = urlencode($return_url);
  header('Location: login.php?RETURN_URL='.$return_url_hex);
  exit();
 }; 

 page_top();

 $config = parse_ini_file('./conf/config.ini', true);
 $app_name = $config['APPLICATION']['NAME'];
 $email_from = $config['EMAIL']['FROM'];
 $max_subscribers = $config['EMAIL']['MAX_SUBSCRIBERS'];
 $full_base_url = $config['SERVER']['FULL_BASE_URL'];

 function validate_params() {
  $subscribers = get_subscribers();
  if (sizeof($subscribers) >= $GLOBALS['max_subscribers']) {
   echo "<p>Sorry - Maximum ({$GLOBALS['max_subscribers']}) subscribers permitted</p>";
   return false;
  } 
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
    $email_literal = escHTML($email);
    echo "<p>Subscriber {$email_literal} Already Added</p>";
    return false;
   }
  }
  return true;
 }

 function update_subscriberss($email) {
  $file_path = './conf/subscribers.txt';
  $file_exists = file_exists($file_path);
  if (!$file_exists) {
   file_put_contents($file_path, null);
  };
  $email_list = [];
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
    array_push($email_list, $email);
    foreach ($data as $row) {
     if ($row[0] != $email) {
      array_push($out_data, $row);
      array_push($email_list, $row[0]);
     }
    };
    rewind($handle);
    ftruncate($handle, 0);
    foreach ($out_data as $row) {
     fputcsv($handle, $row);
    };
    flock($handle, LOCK_UN);
   } else {
    echo "<p>Could not lock the subscriber file for writing</p>";
    return [ false, $email_list ];
   };
   fclose($handle);
  } else {
   echo "<p>Could not open the subscriber file</p>";
   return [ false, $email_list ];
  };
  return [ true, $email_list ];
 };

 function notify_subscribers($email_list, $email) {
  $l = '<ol class="small">';
  foreach($email_list as $subscriber) {
   $subscriber_literal = escHTML($subscriber);
   $l .= "<li>{$subscriber_literal}</li>";
   };
  $l .= "</ol>";
  $style = page_style();
  $message =<<<EOF
 <html>
  <head>
   <style>
{$style}
   </style>
  </head>
  <body>
   <p><h2>Current Subscribers</h2>
   <p>{$l}</p>
   <p>Max Subscribers permitted: {$GLOBALS['max_subscribers']}</p>
   <!-- TRAILER -->
  </body>
 </html>
EOF;
  $headers = "From: {$GLOBALS['email_from']}\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $path = $GLOBALS['full_base_url'];
  foreach($email_list as $subscriber) {
   $email_hex = urlencode($subscriber);
   $links =<<<EOF
  <p>
   <span class="info">
    <a href="{$path}/end_user_unsubscribe.php?EMAIL_ADDRESS={$email_hex}">Unsubscribe</a>
   </span>
  </p>
  <p>
   <span class="info">This email intended for original recipient only</span>
  </p>
EOF;
   $full_message = str_replace('<!-- TRAILER -->', $links, $message);
   mail($subscriber, "{$GLOBALS['app_name']} - New Subscriber: {$email}", $full_message, $headers);
  }
 }

 $subscribers = get_subscribers();

 echo("<h2>Add Subscriber</h2>");

 if (validate_params()) {
  $email = $_POST['EMAIL_ADDRESS'];
  $result = update_subscriberss($email);
  if ($result[0]) {
   $email_literal = escHTML($email);
   echo "<p>Success - User {$email_literal} Added</p>";
   notify_subscribers($result[1], $email);
  };
 };

 page_bottom();

?>