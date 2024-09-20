<?php 
 
 include 'common.php';

 if (! active_session()) {
  $return_url = $_SERVER['PHP_SELF'];
  $return_url_hex = urlencode($return_url);
  header('Location: login.php?RETURN_URL='.$return_url_hex);
  exit();
 }; 

 page_top();
?>

<script>
function form_validation() {
 document.forms[0].addEventListener('submit',
  function(e) {
   let x = document.getElementById('EMAIL_ADDRESS');
   if (!x.value) {
    status_msg("Email Address is Required");
    e.preventDefault()
    return false;
   }
   return true;
  });
}
window.onload = form_validation;
</script>
<h2>Subscribe</h2>
<p>
 Subscribe To Motion Detection Emails.
 <form action="do_subscribe.php" method="POST">
  <dl>
   <dt>Email Address *</dt>
   <dd><input id="EMAIL_ADDRESS" name="EMAIL_ADDRESS" placeholder="Email Address" type="text"></dd>
  </dl>
  <input type="submit" value="Go">
 </form>
</p>

<?php page_bottom(); ?>
