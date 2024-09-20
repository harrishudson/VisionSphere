<?php 

 include 'common.php';

 logoff();

 page_top();

 echo <<<EOF
<h2>Log Off</h2>

<p>You have successfully logged off.  Your session has been terminated.</p>
EOF;

 page_bottom();
?>
