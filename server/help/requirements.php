<?php 

  include '../common.php';

  page_top(true);

?>

<h2>Requirements</h2>
<p>
The requirements for this application are;
<ol>
 <li>At least 1 <b>Raspberry Pi</b> micro computer with a Camera.  Camera equipped Pi Zero's are ideal.</li>
 <li>A <b>web server</b> that supports serving <b>php</b> files.  If your cameras will be deployed 
     publicly, you may need a publicly accessible web hosting service - ideally with your 
     own registered domain.  cPanel hosting services are ideal.  Setting up a web server 
     that can serve php files is beyond the scope of this help documentation here.  
     You are assumed to have access to a web server - whether that is an internal intranet web 
     server or a public web hosting environment.  In fact, if you can read this help page,
     you probably have your basic web server working correctly and you can continue to the 
     next steps.</li>
 <li>If you are doing the Raspberry Pi build/configuration, you may need some basic understanding of;
      <ul>
       <li>Raspberry Pi micro computers running <b>Linux</b> - including; setting up a Pi from scratch and 
           connecting to, or copying files to, your Raspberry Pi.  Eg, some basic familiarity
           with using facilities such as <b>putty</b> and <b>secure copy</b> will be useful.</li>
       <li>Some basic understanding of how web servers work is also handy to have.</li>
      </ul>
  </li>
</ol>
</p>

<?php page_bottom(true); ?>
