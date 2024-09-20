<?php

 include 'common.php';

 $config = parse_ini_file('./conf/config.ini', true);
 $app_name = $config['APPLICATION']['NAME'];
 $email_from = $config['EMAIL']['FROM'];
 $max_subscribers = $config['EMAIL']['MAX_SUBSCRIBERS'];
 $max_emails_per_day = $config['EMAIL']['MAX_SUBSCRIBER_EMAILS_PER_DAY'];
 $full_base_url = $config['SERVER']['FULL_BASE_URL'];

 function validate_params() {
  $cam_label = $_POST['CAM_LABEL'];
  if (is_null($cam_label) or ($cam_label == "")) {
   echo "CAM_LABEL data missing";
   return false;
  };
  $action = $_POST['ACTION'];
  if (is_null($action) or ($action == "")) {
   echo "ACTION data missing";
   return false;
  };
  return true;
 };

 function update_subscribers() {
  $file_path = './conf/subscribers.txt';
  $file_exists = file_exists($file_path);
  if (!$file_exists) {
   file_put_contents($file_path, null);
  };
  $out_data = [];
  $email_list = [];
  $today = date('Ymd');
  if (($handle = fopen($file_path, 'r+')) !== false) {
   if (flock($handle, LOCK_EX)) {
    $data = [];
    while (($row = fgetcsv($handle)) !== false) {
     $data[] = $row;
    }
    foreach($data as $subscriber) {
     $photos_today = 1;
     $send_email = true;
     if (($subscriber[1] == $today)) {
      if ((int)$subscriber[2] >= $GLOBALS['max_emails_per_day']) {
       $photos_today = $subscriber[2];
       $send_email = false;
      } else {
       $photos_today = (int)$subscriber[2];
      };
     };
     $email = $subscriber[0];
     if ($send_email) {
      array_push($email_list, $email);
      if ($subscriber[1] == $today) {
       $photos_today = $photos_today + 1;
      } else {
       $photos_today = 1;
      }
     };
     array_push($out_data, 
                [$email,
                 $today,
                 $photos_today]);
    };
    rewind($handle);
    ftruncate($handle, 0);
    foreach ($out_data as $row) {
     fputcsv($handle, $row);
    };
    flock($handle, LOCK_UN);
   } else {
    echo "Could not lock the subscribers file for writing";
    return [ false, $email_list ];
   };
   fclose($handle);
  } else {
   echo "Could not open the subscribers file";
   return [ false, $email_list ];
  };
  return [ true, $email_list ];
 };

 function notify_subscribers($email_list) {
  $action = escHTML($_POST['ACTION']);
  $subscriber_count = sizeof($email_list);
  $file = escHTML($_POST['FILE']);
  $subject = "{$_POST['CAM_LABEL']} - {$_POST['ACTION']}";
  $style = page_style();
  $message =<<<EOF
 <html>
  <head>
   <style>
{$style}
   </style>
  </head>
  <body>
  <p>
   <h2>{$action}</h2>
  </p>
  <p>
   Sent to {$subscriber_count} Subscribers (max {$GLOBALS['max_subscribers']})
  </p>
  <p>
   <pre>{$file}</pre>
  </p>
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
   mail($subscriber, $subject, $full_message, $headers);
  }
 };

 header('Content-type: text/plain');
 header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
 header("Cache-Control: post-check=0, pre-check=0", false);
 header("Pragma: no-cache");

 if (check_auth($_POST['AUTH_KEY'])) {
  if (validate_params()) {
   $result = update_subscribers();
   if ($result[0]) {
    echo "Success";
    notify_subscribers($result[1]);
   };
  };
 } else {
  sleep(1);
  echo "Authentication failure";
 };

?>
