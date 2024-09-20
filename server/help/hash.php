<?php

 /* Uncomment line below to disable this page */
 // exit();


 include '../common.php';

 page_top(true);

 $TEXT = $_POST['TEXT'];
 if (($TEXT != "") or (!is_null($TEXT))) {
  $TEXT = escHTML($TEXT);
 };

 echo <<<EOF
<script>
function generateRandomString(length = 30) {
 const characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
 let randomString = '';
 for (let i = 0; i < length; i++) {
  randomString += characters.charAt(Math.floor(Math.random() * characters.length));
 }
 return randomString;
}

window.onload = function() {
 document.getElementById('random').addEventListener('click',
  function() {
   document.getElementById('text').value =  generateRandomString();
   document.forms[0].submit();
  });
}

</script>
<h2>Hash Generator</h2>
<p>
It is recommended that an Auth Key or rPi Password be a minimum of 14 characters in length
and ideally more like 20 to 30 characters in length.
</p>

<form action="hash.php" method="POST">
 <input id="text" name="TEXT" value="{$TEXT}" placeholder="Auth Key / Password (Plain Text)" style="width:250px"/>
 <input type="submit" value="Go">
 <button id="random">Random</button>
</form>
EOF;

 if (($TEXT != "") && (!is_null($TEXT))) {
  sleep(1); 
  $text = escHTML($TEXT);
  $hash = escHTML(password_hash($TEXT, PASSWORD_DEFAULT));
  $salt = substr(str_replace('+', '.', base64_encode(random_bytes(1))), 0, 2);
  $pass = escHTML(crypt($TEXT, $salt));
  echo <<<EOF
  <p>
  Auth Key / Password;
  <dl>
   <dt>Plain Text</dt>
   <dd class="result">{$text}</dd>
   <dt>PHP Password Hash</dt>
   <dd class="result">{$hash}</dd>
   <dt>Linux Crypt Hash</dt>
   <dd class="result">{$pass}</dd>
  </dl>
  </p>
EOF;
 };

 page_bottom(true);

?>
