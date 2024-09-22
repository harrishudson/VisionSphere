<?php 

 include 'common.php';

 if (! active_session()) {
  $return_url = $_SERVER['PHP_SELF'];
  $return_url_hex = urlencode($return_url);
  header('Location: login.php?RETURN_URL='.$return_url_hex);
  exit();
 }; 

 page_top();

 echo <<<EOF
 <h2>List Subscribers</h2>
EOF;

 $config = parse_ini_file('./conf/config.ini', true);
 $max_emails_per_day = $config['EMAIL']['MAX_SUBSCRIBER_EMAILS_PER_DAY'];

 $subs = get_subscribers();
 $s = 0;
 if ($subs) {
  $s = sizeof($subs);
 };
 if ($s > 0) {
  echo <<<EOF
 <p>
 <table class="tbl">
 <thead>
  <tr>
   <th>Email</th>
   <th>Photos Sent Today</th>
   <th>Reset Photo Limit</th>
   <th>Do Not Disturb</th>
   <th>Unsubscribe</th>
  </tr>
 </thead>
 <tbody>
EOF;

 foreach($subs as $sub) {
  $f0 = escHTML($sub[0]);
  $f1 = escHTML($sub[1]);

  $max_emails_per_day = $config['EMAIL']['MAX_SUBSCRIBER_EMAILS_PER_DAY'];

  $today = date('Ymd');

  $photos_today = "";

  if ($sub[1] == $today) {
   $f2 = $sub[2];
   if ($f2 == "999") {
    $photos_today = escHTML("Do Not Disturb");
   } else {
    $photos_today = escHTML($f2." / ".$max_emails_per_day);
   };
  }
    
  echo <<<EOF
  <tr>
   <td>{$f0}</td>
   <td>{$photos_today}</td>
   <td>
    <form action="do_photolimit.php" method="POST">
     <input type="hidden" name="EMAIL_ADDRESS" value="${f0}">
     <input type="submit" value="Reset">
    </form>
   </td>
   <td>
    <form action="do_donotdisturb.php" method="POST">
     <input type="hidden" name="EMAIL_ADDRESS" value="${f0}">
     <input type="submit" value="Do Not Disturb">
    </form>
   </td>
   <td>
    <form action="end_user_unsubscribe.php" method="GET">
     <input type="hidden" name="EMAIL_ADDRESS" value="${f0}">
     <input type="submit" value="Unsubscribe">
    </form>
   </td>
  </tr>
EOF;
  };

 echo <<<EOF
  </tbody>
 </table>
 </p>
EOF;
 };
 if ($s == 0) {
  echo "<p>No Subscriber's defined</p>";
 };

 page_bottom();
?>
