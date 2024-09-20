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
      if ($subscriber[1] == $today) {
       $photos_today = $photos_today + 1;
      } else {
       $photos_today = 1;
      }
      array_push($email_list, [ $email, $photos_today ] );
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
  
  // Mime Email Boundary 
  $boundary = md5(time());

  $headers = "MIME-Version: 1.0\r\n";
  $headers .= "From: {$GLOBALS['email_from']}\r\n";
  $headers .= "Content-Type: multipart/related; boundary=\"$boundary\"\r\n";

  // Start the message body
  $message = "--$boundary\r\n";
  $message .= "Content-Type: text/html; charset=UTF-8\r\n";
  $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

  $action = escHTML($_POST['ACTION']);
  $subscriber_count = sizeof($email_list);
  $image = $_POST['PHOTO'];
  $image_type = $_POST['IMAGE_TYPE'];

  $subject = "{$_POST['CAM_LABEL']} - {$_POST['ACTION']}";

  $style = page_style();

  $message .=<<<EOF
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
  Sent To {$subscriber_count} Subscribers (Max {$GLOBALS['max_subscribers']})
 </p>
EOF;
 if ($image != "") {
  $message .=<<<EOF
<p>
 <img src="cid:photo"/>
</p>
EOF;
}
 
  $cam_label = escHTML($_POST['CAM_LABEL']);
  $cam_ip_address = escHTML($_SERVER['REMOTE_ADDR']);
  $application = escHTML($GLOBALS['app_name']);
  $score_threshold = escHTML($_POST['SCORE_THRESHOLD']);
  $score = escHTML($_POST['SCORE']);
  $timestamp = escHTML($_POST['TIMESTAMP']);
  $bom_id = escHTML($_POST['BOM_ID']);
  $bom_wmo = escHTML($_POST['BOM_WMO']);
  $wind_stop = escHTML($_POST['STOP_WIND_KMH']);
  if ($wind_stop != "") {
   $wind_stop .= ' km/h';
  }

  $message .=<<<EOF
 <p>
 <dl class="set">
  <dt>Cam Name</dt>
  <dd>{$cam_label}</dd>
  <dt>Cam IP Address</dt>
  <dd>{$cam_ip_address}</dd>
  <dt>Application</dt>
  <dd>{$application}</dd>
  <dt>Date Time of Recording</dt>
  <dd>{$timestamp}</dd>
  <dt>Noise Threshold Setting</dt>
  <dd>{$score_threshold}</dd>
  <dt>This Image Trigger Noise Value</dt>
  <dd>{$score}</dd>
  <dt>BOM Station ID Setting</dt>
  <dd>{$bom_id}</dd>
  <dt>BOM Station WMO Setting</dt>
  <dd>{$bom_wmo}</dd>
  <dt>Stop When Wind/Gust Above Setting</dt>
  <dd>{$wind_stop}</dd>
EOF;

  $weather_obs = $_POST['WEATHER_OBS'];
  $weather_copyright = "";
  if (($weather_obs != "") and ($weather_obs != '{}')) {
   $weather_obj = json_decode($weather_obs);
   $refresh_message = escHTML($weather_obj->refresh_message);
   $name = escHTML($weather_obj->name);
   $wind_spd_kmh = escHTML($weather_obj->wind_spd_kmh);
   $gust_kmh = escHTML($weather_obj->gust_kmh);
   $air_temp = escHTML($weather_obj->air_temp);
   $mm_rain_since_9am = escHTML($weather_obj->mm_rain_since_9am);
   $weather_copyright = escHTML($weather_obj->copyright);
   $message .=<<<EOF
  <dt>Weather Observations *</dt>
  <dd>
   <dl class="obs">
    <dt>Refresh Message</dt>
    <dd>{$refresh_message}</dd>
    <dt>Name</dt>
    <dd>{$name}</dd>
    <dt>Wind Speed</dt>
    <dd>{$wind_spd_kmh} km/h</dd>
    <dt>Gust Speed</dt>
    <dd>{$gust_kmh} km/h</dd>
    <dt>Air Temperature</dt>
    <dd>{$air_temp} 'C</dd>
    <dt>Rain Since 9am</dt>
    <dd>{$mm_rain_since_9am} mm</dd>
   </dl>
  </dd>
EOF;
   };
  
  $delay = escHTML($_POST['DELAY']);
  $temp = escHTML($_POST['TEMP']);
  $last_config_poll = escHTML($_POST['SINCE_LAST_CONFIG_POLL']);
  $uptime = escHTML($_POST['UPTIME']);

  $message .=<<<EOF
  <dt>Delay Between Captures</dt>
  <dd>{$delay} seconds</dd>
  <dt>Pi Core Temperature</dt>
  <dd>{$temp}</dd>
  <dt>Last Config Poll</dt>
  <dd>{$last_config_poll}</dd>
  <dt>Pi Uptime</dt>
  <dd>{$uptime}</dd>
 </dl>
 </p>
 <p>
 <span class="notice">
  Please Check - Motion Detection Imagery Will NOT Be Sent If; 
  <ol class="small notice">
   <li>Motion Detection Is Stopped</li>
   <li>Image Trigger Noise Value Is Below Noise Threshold Setting</li>
   <li>Wind Or Gust Is Above Wind Stop Speed Setting, Or</li>
   <li>Your Photo Limit For Today Has Been Reached 
       ({$GLOBALS['max_emails_per_day']}/{$GLOBALS['max_emails_per_day']})</li>
  </ol>
 </span>
 </p>
 <!-- TRAILER -->
EOF;

  if ($weather_copyright != "") {
   $message .=<<<EOF
   <p>
    <span class="info">* - {$weather_copyright}</span>
   </p>
EOF;
  };

  $message .=<<<EOF
 </body>
</html>


EOF;
// Note above blank links are important because of mime boundary

  // Attach the image
  if ($image != "") {
   $ext = $_POST['IMAGE_TYPE'];
   $imagePath = "photo.{$ext}";
   $imageContent = chunk_split($image);
   $imageType = "image/{$ext}";

   $message .= "--$boundary\r\n";
   $message .= "Content-Type: {$imageType}; name=\"{$imagePath}\"\r\n";
   $message .= "Content-Transfer-Encoding: base64\r\n";
   $message .= "Content-ID: <photo>\r\n";
   $message .= "Content-Disposition: inline; filename=\"{$imagePath}\"\r\n\r\n";
   $message .= "$imageContent\r\n";
  };
  $message .= "--$boundary--\r\n";

  $path = $GLOBALS['full_base_url'];
  foreach($email_list as $subscriber_tuple) {
   $subscriber = $subscriber_tuple[0];
   $email_hex = urlencode($subscriber);
   $cam_label_hex = urlencode($_POST['CAM_LABEL']);
   $score_threshold_hex = urlencode($_POST['SCORE_THRESHOLD']);
   $bom_id_hex= urlencode($_POST['BOM_ID']);
   $bom_wmo_hex= urlencode($_POST['BOM_WMO']);
   $stop_wind_kmh_hex = urlencode($_POST['STOP_WIND_KMH']);
   $photos_today = escHTML($subscriber_tuple[1]);

   $links =<<<EOF
  <p>
   Actions;
   <ul class="links">
    <li class="pad">
     <a class="btn" href="{$path}/set_config.php?CAM_LABEL={$cam_label_hex}&SCORE_THRESHOLD={$score_threshold_hex}&BOM_ID={$bom_id_hex}&BOM_WMO={$bom_wmo_hex}&STOP_WIND_KMH={$stop_wind_kmh_hex}">Adjust Camera Config</a> (Request Photo/Recording)
    </li>
    <li class="pad">
      <a class="btn" href="{$path}/photolimit.php?EMAIL_ADDRESS={$email_hex}">Reset</a>
      Todays Photo Limit: {$photos_today}/{$GLOBALS['max_emails_per_day']}
    </li>
    <li class="pad">
      <a class="btn" href="{$path}/donotdisturb.php?EMAIL_ADDRESS={$email_hex}">Do Not Disturb</a> 
      For Remainder Of Today
    </li>
    <li class="pad">
      <a class="btn" href="{$path}">Home</a>
    </li>
   </ul>
  </p>
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
   sleep(rand(0,5));
   $result = update_subscribers();
   if ($result[0]) {
    echo "Success.  Request Processed";
    notify_subscribers($result[1]);
   };
  };
 } else {
  sleep(1);
  echo "Authentication failure";
 };

?>
