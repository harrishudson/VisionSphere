<?php 

 include '../common.php';

 page_top(true);
?>

<h2>About</h2>

<h4>Privacy Policy</h4>
<p>
<ul>
 <li>Only standard web logging information will be recorded about your visit to this website.  That includes;      
     your IP address, URL requested, datetime of your request, size of the response and possibly any optional 
     information such as; your user-agent and referring page. No other information is recorded.</li>
 <li>Only a single cookie is used on this site to maintain Logged In user session information.
     This cookie will expire 20 minutes after each successful user authentication.
     No other cookies are used by this application.</li>
 <li>Free text authentication keys are only sent in POST request payloads and, as such,
     should not be present or visible in typical server web logging configurations.</li>
 <li>No Imagery files are stored permanently on client Raspberry Pi's nor stored on your server. 
     Temporary Imagery files only exist on your Raspberry Pi's for the duration of time they are being 
     recorded and then transferred to your server to be ultimately sent on by email.  No Imagery files, 
     permanent or temporary, are stored on your server.</li>
</ul>
</p>

<h4>Author</h4>
<p>
Harris Hudson &copy; 2024
</p>

<h4>Code Repository</h4>
<p>
<a href="https://github.com/harrishudson/VisionSphere">https://github.com/harrishudson/VisionSphere</a>.
</p>

<h4>Donate / Sponsor</h4>
<p>
<a href="https://harrishudson.com/#sponsor">https://harrishudson.com/#sponsor</a>.
</p>

<?php page_bottom(true);  ?>
