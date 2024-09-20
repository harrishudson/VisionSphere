<?php 

 include '../common.php';

 page_top(true);

?>

<h2>Introduction</h2>
<p>
This application is a simple camera motion detection system intended to be run
on Raspberry Pi micro computers.  Uses could vary from security applications, to monitoring
households, to wildlife recording.  Technically, the application simply checks an image 
stream for differences in consecuetive image frames to assess if motion has been detected.  
As it is, this application is largely intended for Australian users but could be modified for 
use in other countries.
</p>
<p>
The philosophy behind this application is to use a minimalist approach in that it does not
require any specialised server side, client side, network router, programming or configuration
to get it up and going.  It is designed so that it can be deployed both within a local network 
or over the internet even if the Raspberry Pi's are connected over mobile broadband.
</p>
<p>At the root of this design philosophy is a periodic polling approach taken by the Raspberry
Pi cameras to a central (ideally public) web host.  The cameras will then request a
configuration payload and take actions accordingly.  This polling approach means that no
specialised client or server coding is required and the application is scalable.  Further,
the application will not require any router configuration changes as there are no inbound
connections made to the Raspberry Pi cameras.  This means, this application should work
over a normal, ADSL, NBN or mobile broadband deployed Raspberry Pi's.
</p>
<p>
A server side hosting environment is required to receive the polling requests from
the Raspberry Pi cameras.  This application is also designed to have a very small network
traffic impact.  And by having a server in the equation, the likelihood of motion detection
mail storms being generated from a poorly configured camera is reduced.  The server side
setup also allows a preset configurable maximum number of subscribers per system to be defined 
(defaults to 8 subscribers but can be changed).  So a single Raspberry Pi camera can 
send motion detection imagery to 8 subscribers.
</p>
<p>
The motion detect imagery is ultimately sent to the end user (subscriber) as an email.  
No imagery is stored on the Raspberry Pi's themselves nor stored on your server 
(only by email).  This allows you to be in control of your imagery and be confident 
your imagery is not being sent to a third party, or to an overseas server, that you might 
otherwise not have control over.
</p>
<p>
Once the Pi cameras are setup and running, the configuration payload that is sent
(polled by) them is simple but comprehensive for most use cases.  The configuration 
payload allows the remote adjustment of the camera motion detect sensitivity (image 
noise setting) and other settings.  Configuration payloads can also request imagery 
and perform some other minor system tasks such as Reboot, conducting a Wifi Scan, 
network Speed Test, or Software Update.
</p>
<p>Configuration payloads can contain BOM Weather Station observation 
information (courtesy of the Australian Bureau of Meteorology) that will enable the
setup of a "Wind Stop" capability.  For example, if the Pi's are setup in an
outdoors environment, the "Wind Stop" capability will enable the Pi's to shut
down motion detection if the Wind or Gust speed is above a certain threshold.
This also mitigates against a possible imagery mail storm under windy conditions.
</p>
<p>
In addition to the actual imagery, emails sent by this application to subscribers
will contain hyperlinks that will easily enable the user to resend a new configuration
to the Pi' cameras.  That is the end users can adjust the configuration and refine
settings until the cameras are 'tuned' to suit their environments.
</p>
<p>Perhaps one of the only downsides to this 'polling' approach is that configuration
request are not quite real time (live).  They are near live but not quite live.  Whilst 
motion detection is live, the polling requests are periodic and not quite live.  By default, 
configuration request frequency is set to every 5 minutes.  This can be adjusted client 
side if a higher, more frequent, update is required.  Such a periodic polling technique
is generally referred to as 'short polling'.  In the future this application may
be modified to make camera request live by using 'long polling' or server events.
</p>
<p>
Hope you will find this application useful.  It has been built with Aussies in mind.
Please see the <a href="about.php">About</a> page for further information about this 
application.
</p>

<?php page_bottom(true); ?>
