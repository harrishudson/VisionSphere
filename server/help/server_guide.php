<?php 

 include '../common.php';

 page_top(true);
?>

<h2>Server Installation and Reference Guide</h2>

<p>
If you can view this web page from your webserver - you have already done the hard part.
To complete your server install, you now need to;
 <ol class="h4">
  <li>Lockdown subdirectories</li>
  <li>Set up conf/config.ini</li>
  <li>AAA Security for this Application - <em>You should read and understand this</em>.</li>
 </ol>
</p>

<h4>Lockdown subdirectories</h4>
<p>
For security, the following subdirectories of this application should not be accessible
from your webserver;
 <ol>
  <li>conf</li>
  <li>cache</li>
 </ol>
If you are using the Apache web server, you may need to setup a <b>.htaccess</b> file
in these subdirectories to prevent public access.  To simplify this, there is a file under
these subdirectories called <b>dot_htaccess</b>.  You will just need to copy this dot_htaccess
file to .htaccess under those subdirectories to prevent web access.  If you are using another
web server other than Apache, you will need to implement your specific web server directives
to prevent web access to these subdirectories.
</p>


<h4>Set up conf/config.ini</h4>
Under the "conf" subdirectory is a file called "config.ini".  To setup your server, you will 
need to edit and configure this file accordingly.  Use a text editor to make changes to 
this file.  The initial (unconfigured) conf/config.ini is as follows;
<p>
 <pre class="terminal" >
[APPLICATION]
NAME = "VisionSphere";

[SERVER]
FULL_BASE_URL = "";

[AUTHENTICATION]
AUTH_KEY_HASH = "";

[EMAIL]
FROM = "";
MAX_SUBSCRIBERS = 8;
MAX_SUBSCRIBER_EMAILS_PER_DAY = 20; </pre>
</p>

<p>
Make the following changes as required;
<ul>
 <li>[APPLICATION]
  <dl>
   <dt>NAME</dt>
   <dd>This field is simply used in the web pages as the main page header.  Generally, this
       doesn't need to be changed.  However, if you will be setting up multiple camera
       SYSTEM's (see next section), then you might want to change this value accordingly.
       Eg, change NAME to "My Beach House Cams"; etc.  This may give more context to 
       subscribers when using the application.</dd>
  </dl>
 </li>
 <li>[SERVER]
  <dl>
   <dt>FULL_BASE_URL *</dt>
   <dd>This is a mandatory required setting.  The value of the FULL_BASE_URL needs to be 
       the full accessible web path to
       where you have installed this application.  That is, your server name and install 
       directory name.
       For example, suppose your web server is called "mydomain123.au" and you have installed
       this application under a public facing web directory called "my_beach_house_cams", then
       this value would be; "https://mydomain123.au/my_beach_house_cams".  You may generally
       need to create this install directory on a web accessible subdirectory on your server.
       Here, unique 
       FULL_BASE_URL values are referred to as a SYSTEM.  By having different install directories,
       you can have multiple SYSTEM's on you domain.  For example, you might have a separate install
       under a directory "my_home_cams" and that FULL_BASE_URL would be; "https://mydomain123.au/my_home_cams".
       On public facing networks, you should use https protocol and not http.  Installing this
       application into your root public facing web directory is also discouraged.  Also, to minimise
       attack vectors, you should perhaps generally minimise providing public links to this end point. 
       <br><br>
       An example of a FULL_BASE_URL that represents an endpoint or install SYSTEM is;
       <br><br>
       FULL_BASE_URL = <span class="result">"https://mydomain123.au/my_beach_house_cams";</span>
   </dd>
  </dl>
 </li>
 <li>[AUTHENTICATION]
  <dl>
   <dt>AUTH_KEY_HASH *</dt>
   <dd>This is a mandatory required setting.
       The authentication model for this application is to use a shared authentication key AUTH KEY
       (or password) that is used for both humans (active subscribers) who connect to the web interface 
       of this application, and it is also used for the Raspberry Pi cameras to authenticate to 
       this application.  On your webserver config.ini file, the value stored here should be 
       a PHP hash of your free text AUTH KEY (or password).  Do not store your free text 
       AUTH KEY here.  Here AUTH KEY and password terminology is used somewhat synonymous.
       To assist in generating a PHP password hash, please consider to use the provided
       <a href="hash.php">Hash Generator</a> and copy-and-paste the value from the "PHP Password Hash"
       value to here.  <em>Note: do not use the "Linux Crypt Hash" here - that is to assist in
       building your Raspberry Pi as is not applicable here.</em> 
       It is recommended that the free text AUTH KEY values be a minimum of 
       14 characters in length or greater.  Your free text AUTH KEY itself should then only be shared
       with your active subscribers and to setup your Raspberry Pi cameras.
       <br><br>
       For example suppose you have decided on an AUTH KEY or password of;
        <span class="result">"MySecretPassword0123";</span>
       <br><br>
       Then by using the Hash Generator, a possible value to paste here of the PHP Password Hash is;
       <br><br>
       AUTH_KEY_HASH = <span class="result">"$2y$10$j1pxllbeg0ZdSse9zqkVFeiOn1b15W9No7zCAtCKl8il9u1HSyFSy";</span>
       <br><br>
       <em>Note: Please use your own AUTH KEY / password and do not copy these examples.</em>
   </dd>
  </dl>
 </li>
 <li>[EMAIL]
  <dl>
   <dt>FROM *</dt>
   <dd>This is a mandatory required setting.
       This value should be a valid and permitted "From" (sender) email address for the automated emails
       that will be sent by this application.  If your server validates email addresses using
       DNS or SPF etc, then this "From" email address must be a valid address that can be
       sent from your server.
       <br><br>
       For example;
       <br><br>
       FROM = <span class="result">"admin@mydomain123.au";</span>
   </dd>
   <dt>MAX_SUBSCRIBERS</dt>
   <dd>This has a default value of 8.  It represents the maximum number of subscribers that can be
       allocated on this SYSTEM.  This can be increased or decreased accordingly.</dd>

   <dt>MAX_SUBSCRIBER_EMAILS_PER_DAY</dt>
   <dd>This has a default value of 20.  This represents the maximum number of emails for a given
       day that any given subscriber will receive before that subscriber must take an action 
       (Reset Photo Limit) in order to receive additional emails for the given day.  This is to 
       mitigate email spam and email storms being generated from perhaps poorly configured, overly 
       sensitive, or wind affected Raspberry Pi motion detection cameras on this SYSTEM.  This 
       can be increased or decreased accordingly.</dd>
  </dl>
 </li>
</ul>
</p>

<p>
<h4>AAA Security for this Application</h4>
AAA Security generally refers to a framework concerning; <b>Authentication</b>,
<b>Authorisation</b> and <b>Accounting</b> for a given computer system.

<h5>Authentication</h5>
For this application, authentication is performed by providing a valid AUTH KEY by active 
subscribers and Raspberry Pi cameras.  The same AUTH KEY will be used in all cases here.  
An active subscriber can;
 <ul>
  <li>Subscribe or unsubscribe, and reset Photo Limits, of any user</li>
  <li>Create/Edit/Change/Delete any camera configuration on the SYSTEM</li>
  <li>Make an Imagery request or System request to any camera on the SYSTEM</li>
 </ul>
An active subscriber can subscribe another user but not provide them with the AUTH KEY.  In which
case such a subscriber is referred to as a "passive" subscriber.  A passive subscriber will then
receive all emails generated by the SYSTEM but the only action a passive subscriber can take is
to unsubscribe from further emails.
<br><br>
For a given SYSTEM, the single AUTH KEY will be shared between all active subscribers and 
cameras on the SYSTEM.  Because of the above actions that an active subscriber can perform,
there has to be some degree of trust between active subscribers on a given SYSTEM.
<br><br>
Also, you should perhaps be aware that any user can unsubscribe from emails without
providing any sort of authentication.  So to reduce attack vectors, you are discouraged
from publishing public links to your SYSTEM's

<h5>Authorisation</h5>
There is no strict authorisation model for this application.  However, a degree of authorisation
can be implemented by using different SYSTEM's.  Here a SYSTEM is defined as the unique combination
of the FULL_BASE_URL endpoint.  A Raspberry Pi camera can only be configured to communicate with
a single endpoint.  So, by using different servers or multiple install directories on the same
server, you can setup multiple SYSTEM's.  Then you can provide different SYSTEM AUTH KEY's to
different sets of end user active subscribers.
<br><br>
For example, you could have 2 different SYSTEM's, or install endpoints, such as;
 <ol>
  <li>https://mydomain123.au/my_beach_house_cams</li>
  <li>https://mydomain123.au/my_home_cams</li>
 </ol>

<h5>Accounting</h5>
The accounting model for this application is normal web log accounting.  You can refer to
your server web logs to check activity.  Further, no free text passwords will be sent to
endpoints using GET query strings - so free text passwords should not appear in your web logs.
<br><br>
However, there is no fine grained accounting with respect to which active subscribers performed
which action on a given SYSTEM.  Again, there needs to be some degree of trust between
active subscribers on a given SYSTEM - otherwise break them out to separate SYSTEM's as 
mentioned under the Authorisation model above.
</p>

<?php page_bottom(true);  ?>
