<?php

 $session_timeout = 1200;

 session_start(['cookie_lifetime' => $session_timeout]);
 //session_start();

 function escHTML($s) {
  if ((is_null($s)) or ($s == "")) {
   return $s;
  }
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
 };

 function logon() {
  session_regenerate_id(true);
  $_SESSION['auth_timestamp'] = time();
 };

 function logoff() {
  unset($_SESSION['auth_timestamp']);
  session_unset();
  session_destroy();
 };

 function active_session() {
  if (isset($_SESSION['auth_timestamp'])
    && $_SESSION['auth_timestamp'] > time() - $GLOBALS['session_timeout']) {
   return true;
  };
  return false;
 };

 function check_auth($password) {
  $config = parse_ini_file('./conf/config.ini', true);
  $current_hash = $config['AUTHENTICATION']['AUTH_KEY_HASH'];
  return (password_verify($password, $current_hash));
 };


 function get_subscribers() {
  $subs_file = './conf/subscribers.txt';
  if (! file_exists($subs_file)) {
   return null;
  };
  $subscribers = array_map('str_getcsv', file($subs_file));
  return $subscribers;
 }

 function get_cams() {
  $cams_file = './conf/cam_configs.txt';
  if (! file_exists($cams_file)) {
   return null;
  };
  $cams = array_map('str_getcsv', file($cams_file));
  return $cams;
 }

 function page_style() {
  $style =<<<EOF
body { 
 font-family: Arial, Helvetica, sans-serif; 
 max-width: 1200px;
}
input[type="submit"], button, .btn {
 border: 1px solid white;
 border-radius: 4px;
 background-color: #0066b2; 
 color: white;
 text-decoration: none;
}
input[type="submit"]:hover, button:hover, .btn:hover {
 background-color: #43a9e5; 
}
input[type="submit"], button  {
 padding: 6px;
}
.btn {
 padding: 2px;
}
li, dd { 
 margin: 6px;
}
h4, .h4 {
 color: #000080; 
}
table, th, td {
 border: 1px solid;
 border-collapse: collapse;
 padding: 2px;
}
th {
 background-color: #eeeeee;
}
img {
 max-width: 95%; 
}
ol.small {
 font-size: smaller;
}
dl.set {
 font-size: smaller;
}
dl.obs dt {
 color: black !important;
}
dl.set dd {
 color: dodgerblue;
 min-height: 12px;
}
dl.obs {
 margin-left: 12px;
}
dl.obs dd {
 color: olive !important;
}
.notice {
 font-size: smaller;
 color: #3333AA;
}
.info {
 font-size: x-small;
 font-style: italic;
}
.links {
 list-style-type: none;
}
.pad {
 margin: 8px;
}
.result {
 color: olive;
 font-family: monospace;
}
.mono {
 font-size: larger;
 font-family: monospace;
}
EOF;
  return $style;
 };

 function get_home($is_subdir = false) {
  $home = "./";
  if ($is_subdir) {
   $home = "../";
  };
  return $home;
 };

 function page_top($is_subdir = false) {
  $home = get_home($is_subdir);
  $config = parse_ini_file($home.'/conf/config.ini', true);
  $app_name = escHTML($config['APPLICATION']['NAME']);
  $style = page_style();
  echo <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
 <title>{$app_name}</title>
 <meta name="author" content="Harris Hudson. harris@harrishudson.com">
 <meta id="application-name" name="application-name" content="{$app_name}">
 <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes">
 <meta charset="UTF-8">
 <link rel="icon" href="{$home}/favicon.svg">
 <style>
{$style}
 </style>
 <style>
.terminal {
 border:2px solid green;
 background-color: black;
 color:white;
 font-family: monospace;
 font-size: 12px;
 max-width: 640px;
 padding: 4px;
}
ul.status {
 position: fixed;
 bottom: 0px;
 right: 0px;
 width: 350px;
 list-style-type: none;
 list-style-position: outside;
 pointer-events: none;
 z-index: 9000;
}
ul.status>li {
 color: white;
 border: 2px solid rgb(51 80 117);
 border-radius: 5px;
 margin: 4px;
 padding: 4px;
 background-color: rgb(129 131 182);
 opacity: 0.95;
 pointer-events: none;
 z-index: 9000;
}
.status_right {
 opacity: 0.2;
 transform: translateX(500px);
 transition: all 0.5s cubic-bezier(.36,-0.64,.34,1.76);
}
.status_show {
 opacity: 1;
 transform: none;
 transition: all 0.5s cubic-bezier(.36,-0.64,.34,1.76);
}
 </style>
 <script>
 function status_msg(msg) {
  let status_queue = document.getElementById('status_queue')
  let el = document.createElement('li')
  el.className = "status_right"
  el.innerText = msg
  status_queue.appendChild(el)
  window.setTimeout(function () {
   el.className = "status_right status_show"
   window.setTimeout(function () { status_destroy(el) }, 5000)
  }, 80)
 }
 function status_destroy(el) {
  el.className = "status_right"
  window.setTimeout(function () { el.remove() }, 550)
 }
 </script>
</head>
<body>
<h1>{$app_name}</h1>
EOF;
 };

 function page_bottom($is_subdir = false) {
  $home = get_home($is_subdir);
  echo <<<EOF
<p>
 <a href="{$home}">Home</a>
</p>
 <ul id="status_queue" class="status"></ul>
</body>
</html>
EOF;
 };

?>
