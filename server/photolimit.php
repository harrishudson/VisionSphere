<?php 
 
 include 'common.php';

 if (! active_session()) {
  $return_url = $_SERVER['PHP_SELF'];
  $param_email_address = urlencode($_GET['EMAIL_ADDRESS']);
  $query_string = "?EMAIL_ADDRESS=".$param_email_address;
  $full_return_url = $return_url.$query_string;
  $full_return_url_hex = urlencode($full_return_url);
  header('Location: login.php?RETURN_URL='.$full_return_url_hex);
  exit();
 }; 

 page_top();
?>

<h2>Reset Your Photo Limit For Today</h2>

<script>
function form_validation() {
 document.forms[0].addEventListener('submit',
  function(e) {

   let email = document.getElementById('EMAIL_ADDRESS')
   if (!email.value) {
    status_msg("Email Address is Required")
    e.preventDefault()
    return false;
   }

   return true;
  });
}
window.onload = form_validation;
</script>

<?php

 $email_address = escHTML($_GET['EMAIL_ADDRESS']);

 echo <<<EOF
  <form action="do_photolimit.php" method="POST">
   <dl>
    <dt>Email Address *</dt>
    <dd><input id="EMAIL_ADDRESS" name="EMAIL_ADDRESS" 
         value="{$email_address}" placeholder="Email Address"></dd>
   </dl>
  <p>
   <input id="submit" type="submit" value="Set">
  </p>
  </form>
EOF;

 page_bottom();
?>
