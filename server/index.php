<?php
 include 'common.php';
 page_top();
?>

<script>
function upgrade_connection() {
 if (location.protocol == 'http:') {
  status_msg('Insecure page load detected (http). \n'+
             'Reloading as secure (https) in 10 seconds.')
  window.setTimeout(
   function() {
    location.href = 'https:' + 
                     window.location.href.substring(window.location.protocol.length)
  }, 10000)
 }
}
window.onload = upgrade_connection
</script>

<h2>Home</h2>
<p>
 <ul>
  <li><a href="login.php">Authenticate Key / Log In</a></li>
  <li><a href="subscribe.php">Subscribe</a></li>
  <li><a href="set_config.php">Create/Set/Edit Cam Config</a></li>
  <li><a href="list_cams.php">List Cams</a></li>
  <li><a href="photolimit.php">Reset Photo Limit</a></li>
  <li><a href="donotdisturb.php">Do Not Disturb</a></li>
  <li><a href="list_subscribers.php">List Subscribers</a></li>
  <li><a href="logoff.php">Log Off</a></li>
  <li>Help and Documentation;
   <ul>
    <li><a href="help/intro.php">Introduction</a></li>
    <li><a href="help/requirements.php">Requirements</a></li>
    <li><a href="help/server_guide.php">Server Installation and Reference Guide</a></li>
    <li><a href="help/client_guide.php">Client Installation and Reference Guide</a></li>
    <li><a href="help/user_guide.php">Application User Guide</a></li>
    <li><a href="help/hash.php">Hash Generator</a></li>
    <li><a href="help/about.php">About</a></li>
   </ul>
  </li>
 </ul>
</p>

<?php page_bottom(); ?>
