<?php 

 include '../common.php';

 page_top(true);
?>

<h2>Application User Guide</h2>

<p>
This user guide will briefly describe how to use the following pages that make up
the web portal component of this application.  Namely;
</p>
<ol class="h4">
 <li>Authenticate Key / Log In</li>
 <li>Subscribe</li>
 <li>Create/Set/Edit Cam Config</li>
 <li>List Cams</li>
 <li>Reset Photo Limit</li>
 <li>Do Not Disturb</li>
 <li>List Subscribers</li>
 <li>Log Off</li>
</ol>

<hr>

<h4>Authenticate Key / Log In</h4>
<p>
This page is to facilitate a Log In and enable you to use the other
functionality here.  In this web portal, Log In sessions are active
for 20 minutes.  If you Log Off, or after 20 minutes has elapsed,
your Log In session will be terminated (and you will need to re-authenticate
to continue).  The input fields for this page are;
</p>

<h5>Auth Key</h5>
Enter your shared Auth Key (or Password) here.  This is the Auth Key
that has been setup for this SYSTEM and is shared with other active 
subscribers of this SYSTEM, and also used by the Raspberry Pi Cameras 
to connect.

<hr>

<h4>Subscribe</h4>
<p>
This page is used to subscribe a user to the SYSTEM.  
The input fields for this page are;
</p>

<h5>Email Address</h5>
Enter your email address or another persons email address that
you wish to subscribe to this motion detection SYSTEM.  Those
users will then receive emails sent from the Raspberry Pi cameras
on this SYSTEM.  Be careful when entering email addresses to
ensure no typo mistakes.  All subscribers are notified by email
when a new user is subscribed to the SYSTEM.

<hr>

<h4>Create/Set/Edit Cam Config</h4>
<p>
This page is used to prepare a configuration payload to
be sent to a Raspberry Pi Camera.  This page may be linked
to from various resources or may be invoked directly.
The input fields for this page are;
</p>

<h5>Cam Name</h5>
This is the name of the Raspberry Pi Camera.  This is to
identify which camera on this SYSTEM this configuration 
payload is for.
Which may be the Raspberry Pi hostname or the Camera Name 
if configured on the Raspberry Pi.  If you are invoking 
this page from scratch then the entered Cam Name must 
match exactly the same as to what is configured on the 
Raspberry Pi.

<h5>Image Noise Threshold</h5>
This is the Image Noise Threshold tolerance setting that will be used to
govern when image motion is considered to be detected.  That is, this
is the image pixel vector differences tolerance for considering motion 
between n subsequent image frames.  This is a relative number and has
a default value of 20.  This setting is perhaps one of the primary 
parameters to tune your camera.  Tuning your camera may be required 
to achieve optimum results which minimise the likelihood of false 
detection emails but capture images when images are subject to motion.
The smaller this value, the more likely motion detection will be triggered.
With higher values meaning reduced sensitivity.  Values should not be 
below zero.  In fact, tuning this value accurately may be crucial for the 
end user experience so some further guidelines are listed here;
<br>

<ul>
 <li>If your cameras will be used inside of a household or building,
     this sensitivity value may be able to be dialled right down as
     the camera is likely to be less affected by background movement.</li>
 <li>If your camera will be used to detect large objects such as humans
     or vehicles in an outdoor context (eg, entrance door of a house or 
     a driveway camera for example), then this sensitivity value may need 
     to be increased until the desired result is achieved.  Also, because 
     it is outside, you may wish to configure a Weather <span class="mono">Wind Stop</span>
     setting.  
     if used in conjunction with a Weather <span class="mono">Wind Stop</span> 
     setting (see below) - 
     it may be possible to configure a fairly high <span class="mono">Wind Stop</span>
     setting.</li>
 <li>If your camera will be used in an outdoor context with the intention 
     to capture wildlife, you may need to experiment and dial this value 
     down to be able to detect small animals.  However, this may also 
     necessitate configuring a fairly low Weather <span class="mono">Wind Stop</span> 
     setting also (see below).</li>
 <li>Outdoor cameras used at night under artificial light may possibly emit
     higher image noise levels and, as such, they may require an increased noise
     threshold setting to minimise the likelihood of false triggers.</li>
</ul>
As a general guideline, in the process of tuning - if you are receiving 
emails for very minor changes in motion (that are essentially false detection's), 
then it is suggested to progressively increase this setting until the frequency
of false detection emails are reduced.  If the camera is outside, then this 
tuning may also need to be done in conjunction with simultaneously tuning the 
Weather <span class="mono">Wind Stop</span> setting until a desired combination 
is found that minimises false triggering.  

<h5>BOM Station State ID &amp; BOM Station WMO</h5>
At the time of publishing this application, these 2 fields are provided in
the camera configuration to allow the linking in of BOM (Bureau of Meteorology)
weather station wind/gust observation information.  This data is published
on the BOM website (and ftp server) but is subject to change so this section 
is applicable at the time of publishing this application (September 2024).  
This is to allow Australian residents to configure the Weather 
<span class="mono">Wind Stop</span> functionality.  This section has been 
updated (October 2025) with new instructions to cater for new changes due to 
be deployed to the BOM website.

<br><br>  
In order to setup a <span class="mono">Wind Stop</span> setting for your camera,
it is necessary to link in the BOM observation data for a given BOM weather station.
BOM weather stations can be identified by 2 ID's.  Namely; 
<ol>
 <li>BOM Station State ID (BOM state file product identifier)</li>
 <li>BOM Station WMO ID (International Weather Station ID)</li>
</ol>
<br>
After the rollout of the new BOM website (October 2025), finding such ID's for a given 
weather station may be more difficult to achieve.  Please note that the mechanism used
by this application, after the 2025 rollout of the new BOM website, has changed from
using JSON endpoints to now use an FTP endpoint.  As such, the BOM state based 
product codes have changed.  So if you have a prior weather configuration defined, 
you may need to update it with the new BOM product codes.  So, there are now 2 options 
to allow you to configure a BOM weather station observations and link them to your
cameras in this application;

<ol>
 <li><span class="h4">Manual Configuration</span><br>
     For manual configuration, you will need to manually identify the BOM Station State 
     ID and the International Weather Station ID (wmo_id) for the station you wish to
     use.  This application now uses the BOM Published ftp 72 Historical Observation data 
     to obtain weather observation data.  Please refer to this link; 
     <a href="https://reg.bom.gov.au/catalogue/72_hr_historical_obs.pdf">
      https://reg.bom.gov.au/catalogue/72_hr_historical_obs.pdf</a> for more information.
     You will need to ascertain which state file archive (product) to extract, manually 
     extract that file, and then check the file listings to identify your particular 
     station wmo_id.  Once you have determined those 2 ID's, you can enter them into the 
     2 input fields here.
 </li>
 <li><span class="h4">Auto Configuration Using the Map</span><br>
   This application now includes a <a href="../weather_observations_map.php">
    Weather Observations Map</a> (see below).  
   You can initially leave
   these 2 BOM input values blank.  Then use the "Weather Observations Map" to
   show all BOM weather stations on a map.  Zoom the map in to where your Pi
   camera will be deployed.  Decide on a nearby weather station.  Then tap/click 
   on a map marker (station), to display a popup of the latest weather observations.  
   Further, if you scroll down in the popup, there is a simple mechanism to allow 
   observations from this BOM weather station to be linked into an existing 
   (already created) camera definition.  Behind the scenes, that will populate these 
   2 required BOM ID's into the camera definition.
   However, using this map makes it very easy to link in weather observation data to an 
   existing camera (without having to worry about determination of the required BOM ID's).
 </li>
</ol>

So, as a manual example, if we are going to choose to use this BOM weather station wind/gust 
information for our Raspberry Pi camera deployed somewhere near Braidwood NSW, use these 2 
identifiers accordingly for these 2 fields.  
<br><br>
That is;
<dl>
 <dt>BOM Station State ID</dt>
 <dd>IDN60910 (NSW & ACT)</dd>
 <dt>BOM Station WMO</dt>
 <dd>94927</dd>
</dl>

<h5>Stop When Wind Above</h5>
This is generally referred to as a <span class="mono">Wind Stop</span> setting.
Now if you have configured the BOM weather station details (previous 2 inputs), then
this input field becomes applicable.  Otherwise, if no BOM details are provided (previous
2 inputs) this field is ignored.  The default value is 20.  Once this information is
passed to the Raspberry Pi - under normal motion detection conditions, the Pi will 
temporarily pause (or stop) recording motion detection if either the weather station
"Wind" or "Gust" speed (in kmh) is above this value.  Should the "Wind" or "Gust" speed from
subsequent later configuration polls by the camera fall back down below this threshold,
then motion detection will resume again.  That is, this is a threshold that can be
set by a subscriber to have the camera perform a "Wind Stop" if weather conditions
are windy.  This allows complete remote configuration of a particular BOM weather station 
and <span class="mono">Wind Stop</span> threshold settings.
<br><br>
This <span class="mono">Wind Stop</span> setting and the BOM Identifiers above are optional 
settings.  If your camera is used indoors a Weather <span class="mono">Wind Stop</span> 
setting may not be required.
If your camera is used outdoors and there are fairly large background
objects in the field of view (such as trees and/or large portions of the sky), 
then it may be worthwhile to setup a <span class="mono">Wind Stop</span> setting so your camera 
can temporarily shut down (pause) motion detection in periods of high winds and gusts.
<br><br>
As somewhat mentioned in the above input field <span class="mono">Image Noise Threshold</span>,
this <span class="mono">Wind Stop</span> setting may require some tuning and experimentation
until the correct combination can be found for your local conditions to help minimise false
motion triggers.

<h5>Camera</h5>
The <span class="mono">Camera</span> is a select input field that enables a small set of
instructions for the Raspberry Pi motion detection program to be queued.  The 
instructions for the motion detection program are as follows;

<dl>
 <dt>Select</dt>
 <dd>No action</dd>
 <dt>Start</dt>
 <dd>This will start or resume the normal motion detection for the camera.  This is
     generally only applicable if the motion detection had otherwise been stopped.
     An email with a photograph will be sent upon a Start</dd>
 <dt>Stop</dt>
 <dd>This will stop, or pause, the normal motion detection for the camera.  No motion
     detection emails will be sent once a camera has been stopped.  Cameras will remain
     in this "stopped" state until any of the following occur; the camera is restarted, 
     a photo or recording is requested, or the Raspberry Pi is rebooted.</dd>
 <dt>Take Photo</dt>
 <dd>This will request a photo to be taken by the camera.  The camera will then take a
     photo in the next configuration poll and will also automatically resume motion detection.</dd>
 <dt>Take Recording</dt>
 <dd>This will request a video to be recorded by the camera.  The camera will then take a
     video recording in the next configuration poll and will also automatically 
     resume motion detection.</dd>
</dl>
 
<h5>System</h5>
The <span class="mono">System</span> is a select input field that enables a small set of
instructions for the Raspberry Pi itself to be queued.  The instructions for the 
Raspberry Pi system are as follows;

<dl>
 <dt>No Action</dt>
 <dd>No Action</dd>
 <dt>Reboot</dt>
 <dd>This will request the Raspberry Pi to reboot and restart normal motion detection.</dd>
 <dt>Speed Test</dt>
 <dd>This will request the Raspberry Pi to conduct a network speed test and then email
     the results to all subscribers of the SYSTEM.</dd>
 <dt>Wifi Scan</dt>
 <dd>This will request the Raspberry Pi to conduct a network Wifi Scan and then email
     the results to all subscribers of the SYSTEM.</dd>
 <dt>Update</dt>
 <dd>This will instruct the Raspberry Pi to fetch a copy of the motion detection python
     program <span class="mono">motion.py</span> from under the <span class="mono">client_software</span>
     web accessible subdirectory and then reboot.  This is intended to allow customised updates
     to the core motion detection software to be performed remotely.</dd>
</dl>

<hr>

<h4>List Cams</h4>
<p>
This page will list Raspberry Pi camera configurations that are presently stored on your server
by way of a simple table.  The fields displayed are as follows;
</p>

<dl>
 <dt>Name</dt>
 <dd>This is the unique Cam Name</dd>
 <dt>Noise Threshold</dt>
 <dd>The Noise Threshold setting to be used for the Camera.</dd>
 <dt>Pending Camera Action</dt>
 <dd>This is any Camera Action that is pending (yet to be sent) to the Camera.  Eg; <em>Start</em>, 
     <em>Stop</em>, <em>Take Photo</em> or <em>Take Recording</em>.
     If no action is queued, this will be <em>None</em>.  Is set to <em>None</em> after the 
     next configuration poll by the Camera.</dd>
 <dt>Pending System Action</dt>
 <dd>This is any System Action that is pending (yet to be sent) to the Camera.  Eg; <em>Reboot</em>, 
     <em>Speed Test</em>, <em>Wifi Scan</em> or <em>Update</em>.
     If no action is queued, this will be <em>None</em>.  Is set to <em>None</em> after the 
     next configuration poll by the Camera.</dd>
 <dt>BOM Station State ID</dt>
 <dd>The value of the BOM Station State ID for the Camera which may be used in relation to a 
     <span class="mono">Wind Stop</span> configuration.</dd>
 <dt>BOM Station WMO ID</dt>
 <dd>The value of the BOM Station WMO for the Camera which may be used in relation to a 
     <span class="mono">Wind Stop</span> configuration.</dd>
 <dt>Wind Stop Km/h</dt>
 <dd>The value of the <span class="mono">Wind Stop</span> setting.</dd>
 <dt>Last Config Poll</dt>
 <dd>This is the date and time of the last configuration poll from the Camera.  
     Ignoring any immediate motion detection's, this
     is generally the last time the server "heard" from the Camera.</dd>
 <dt>Edit</dt>
 <dd>This will take you directly to the Camera Configuration page with all these default values 
     populated so that a new or updated configuration can be done.</dd>
 <dt>Delete</dt>
 <dd>This will delete (remove) this configuration for this Camera from the server.  Note that 
     if a Raspberry Pi Camera is still on the network, it will re-push it's current configuration 
     to the server.</dd>
</dl>

<hr>

<h4>Reset Photo Limit</h4>
<p>
This page is used to reset a photo limit for today for a given
subscriber of the SYSTEM. 
In order to mitigate against spam or email storms for poorly configured
or wind affected cameras, there is a limit to the total number of emails
a subscriber can receive per day.  Refer to the
<a href="server_guide.php">Server Installation and Reference  Guide</a>
for more details concerning the <em>MAX_SUBSCRIBER_EMAILS_PER_DAY</em>
setting.  The input fields for this page are;
</p>

<h5>Email Address</h5>
Enter your email address or another subscribers email address that
you wish to reset the photo limit for today.  If a subscriber has
reached their photo limit for today, then once reset, they will
then be able to receive additional emails for today.

<hr>

<h4>Do Not Disturb</h4>
<p>
This page is used to set a subscriber photo count to <em>999</em>
which, in effect, will max out that users photo limit for today
and prevent any additional emails being sent to them by the SYSTEM
unless their photo limit is reset.  The input fields for this
page are;
</p>

<h5>Email Address</h5>
Enter your email address or another subscribers email address that
you wish to not receive further emails from any cameras on
the SYSTEM for today.

<hr>

<h4>List Subscribers</h4>
<p>
This page will list subscribers of the SYSTEM by way of a simple table.  
The fields displayed are as follows;
</p>

<dl>
 <dt>Email</dt>
 <dd>The subscriber email address</dd>
 <dt>Photos Sent Today</dt>
 <dd>Today's photo count for this subscriber along with the maximum permitted number.</dd>
 <dt>Reset Photo Limit</dt>
 <dd>Will take you to the <em>Reset Photo Limit</em> page for this subscriber.</dd>
 <dt>Do Not Disturb</dt>
 <dd>Will take you to the <em>Do Not Disturb</em> page for this subscriber.</dd>
 <dt>Unsubscribe</dt>
 <dd>Will unsubscribe (remove) this user from the system.  Note that when any user is 
     unsubscribed an automatic email is generated to notify all remaining subscribers.</dd>
</dl>

<hr>

<h4>Weather Observations Map</h4>
<p>
This will load a Leaflet Map with a per station summary of some of the latest weather
observations on a station basis for weather station information from the Bureau of Meteorology (BOM).
Markers will be displayed on the map for each station location.  And the fill color for the
stations is an indicator of the latest recorded air temperature.  Hovering over a map marker 
will display a tooltip of the station name and latest observed temperature.  
Clicking/tapping on a station map marker, will open a popup on the map that reveals additional 
latest observation data.  Note that this is just a small summary of all the data available on the BOM.
Scrolling down to the bottom of the popup will reveal an entry labelled; 
<b>Link Station Obs to Cam</b>.  Under this will be a select list of your current cams.  By selecting 
your particular cam here, you can rapidly (automatically) link the station observations to your camera.  
That is you do not need to worry about entering the actual BOM Station State ID nor BOM Station WMO ID.  
This will facilitate automatic linking.
</p>
<p>
The data in this map page will refresh every 3 minutes.  However, only authorised (logged in) users
are permitted to use this page.  As such, you may need to re-authenticate after every 20 minutes to
keep the functionality of this map working correctly.
</p>

<hr>

<h4>Log Off</h4>
<p>
This page can be used to explicitly terminate your authenticated session.
</p>

<?php page_bottom(true); ?>
