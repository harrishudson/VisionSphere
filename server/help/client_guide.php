<?php 

 include '../common.php';

 page_top(true);
?>

<h2>Client Installation and Reference Guide</h2>

<p>
Client Installation and Build involves building and configuring your Raspberry Pi cameras.
You can build multiple Raspberry Pi cameras to use a single Server SYSTEM.  The core
steps are;
 <ol class="h4">
  <li>Optionally Download Raspberry Pi OS Image</li>
  <li>Burn Image to micro SD card - <em>with optional manual headless setup</em></li>
  <li>Log In (ssh) to your Pi</li>
  <li>Set up hostname and timezone - <em>if not already done</em></li>
  <li>Install dependent packages</li>
  <li>Install client side programs for this application</li>
  <li>Setup watchdog</li>
  <li>Configure config.ini</li>
  <li>Setup crontab</li>
  <li>Reboot and Finish</li>
  <li>Appendix 1 - Manual Headless Setup</li>
  <li>Appendix 2 - Auxiliary client programs</li>
 </ol>
</p>

<h4>Optionally Download Raspberry Pi OS Image</h4>
<p>
This step is optional.  You may need to download a Linux based OS for your 
Raspberry Pi if you decide to not use the <b>Raspberry Pi Imager</b> 
(see next section).  
If you decide to use the Raspberry Pi Imager you can omit this step.
If your Pi will only be used for this application (camera motion detection), 
then a "Lite" version of the OS is recommended.  The following client side 
instructions in this page have been tested and verified to work correctly using 
"Raspberry Pi OS (Legacy) Lite" against a Raspberry Pi Zero W using the
 July 4th 2024 release.  Namely;
<pre>
Raspberry Pi OS (Legacy) Lite
Release date: July 4th 2024
System: 32-bit
Kernel version: 6.1
Debian version: 11 (bullseye)
</pre>
</p>


<h4>Burn Image to micro SD card - <em>with optional manual headless setup</em></h4>
<p>
For most cases, you might find the Raspberry Pi Imager the easiest way to
burn your OS Image to your micro SD card for use in your Pi.  You may need to
download and install that Imager.  The Raspberry Pi Imager 
allows the following to be preconfigured using the <span class="mono"><em>SETTINGS</em></span> 
tab in the Imager;
<ul>
 <li>Hostname</li>
 <li>Username and password</li>
 <li>Wifi credentials</li>
 <li>Timezone</li>
 <li>And to enable password authenticated SSH</li>
</ul>

You can select the above mentioned OS version in the Raspberry Pi Imager by
selecting; <br><br>
<span class="mono"><em>CHOOSE OS</em></span> &rarr; 
<span class="mono"><em>Raspberry Pi OS (other)</em></span> &rarr; 
<span class="mono"><em>Raspberry Pi OS (Legacy, 32bit) Lite.</em></span>

<br><br>
However, the instructions contained in this page do not assume you have used the Raspberry Pi Imager and
cater for the case that you have done a full manual configuration and burn of your OS image to your 
micro SD card.  This is because, there may be cases such as the need to configure multiple, or 
password-less, wifi networks using a headless setup for your Pi.  In which case the Raspberry Pi 
Imager may not be suited.
<br><br>
In such cases, if you will be doing a full manual burn of your OS image, please refer to 
<a href="#appendix-1">Appendix 1 - Manual Headless Setup</a> section below.
Some of the continued instructions below will assume you have done a full manual burn of your 
OS Image to your Pi and as such they may be able to skipped if you have used the Raspberry Pi Imager.
</p>

<h4>Log In (ssh) to your Pi</h4>
<p>
Once you have placed the micro SD card in your Pi, power it up, wait 1 minute, then connect to
it using your favourite ssh client (such as <b>putty</b>).  You may need to download the
putty application if you have not connected to a Pi previously.  If you have used the 
Raspberry Pi Imager and preconfigured the hostname and ssh access, you should be able to 
ssh to that hostname.
<br><br>
If you have done a manual burn of your OS to the Pi micro SD card, and not preconfigured a hostname,
then try to connect to a hostname called <span class="mono"><b>raspberrypi</b></span>.  You may need to 
check your router admin web portal to verify your Pi has connected to your local wifi network if 
you have trouble here.
<br><br>
Once logged on, by entering your username and password, you should have the 
familiar <span class="mono" style="color:blue;">$_</span> terminal prompt displayed.
</p>

<h4>Set up hostname and timezone - <em>if not already done</em></h4>

This step is optional.  If you have used preconfigured settings for your hostname
and timezone and used the Raspberry Pi Imager - you can omit this step and go
to the next.  The instructions here are for the case you have done a manual burn
of your Raspberry Pi OS.

Once logged on to your Pi, issue the following command at the 
 <span class="mono" style="color:blue;">$_</span> prompt;
<br><br>
<span class="mono"><span style="color:blue;">$ </span>sudo raspi-config</span>
<br><br>
This will present the raspi-config administration menu that can be navigated using the
arrows, tab and return keys.
<dl>
 <dt>To change the hostname</dt>
 <dd>
  <span class="mono"><em>System Options</em></span> &rarr; 
  <span class="mono"><em>Hostname</em></span> 
 </dd>
 <dt>To change the timezone</dt>
 <dd>
  <span class="mono"><em>Localisation Options</em></span> &rarr; 
  <span class="mono"><em>Timezone</em></span> 
 </dd>
</dl>
If you didn't reboot as part of exiting raspi-config, reboot your Pi;
<br><br>
<span class="mono"><span style="color:blue;">$ </span>sudo reboot</span>
<br><br>
Wait 1 minute then reconnect to your Pi using the new hostname.

<h4>Install dependent packages</h4>
This application requires use of the <b>ffmpeg</b> video processing
facility and the <b>speedtest-cli</b> network speed test facility.
These may not already be installed on your OS.  To install these facilities,
issue the following commands;
<br><br>
<span class="mono"><span style="color:blue;">$ </span>sudo apt-get update</span>
<br>
<span class="mono"><span style="color:blue;">$ </span>sudo apt install ffmpeg -y</span>
<br>
<span class="mono"><span style="color:blue;">$ </span>sudo apt install speedtest-cli</span>
<br>

<h4>Install client side programs for this application</h4>

Using whichever method you are comfortable with (such as <b>git clone</b>, <b>wget</b> or <b>secure copy</b>),
copy all the files from the code base repository <span class="mono"><b>client</b></span> subdirectory to your 
Raspberry Pi.  If you have never copied files to a Raspberry Pi before - consider to use the <b>secure copy</b>
or <b>scp</b> facility that comes with <b>putty</b>.<br><br>
Eg, copy the files from this location to your Raspberry Pi home directory;<br><br>
<span class="mono"><em>https://github.com/harrishudson/VisionSphere</em> &rarr; 
<em>client</em></span><br><br>
These are the files that should be copied to your Pi;
<ul class="links mono" style="font-size: smaller;">
 <li>bomb.py</li>
 <li>common.py</li>
 <li>config.dat</li>
 <li>config.ini</li>
 <li>crontab.l</li>
 <li>fetchconfig.py</li>
 <li>motion.py</li>
 <li>motion_logger.py</li>
 <li>motion_restarter.sh</li>
 <li>ping_reboot.py</li>
 <li>resolutions.py</li>
</ul>


<h4>Setup watchdog</h4>
This step is optional.  For this motion detect application, there may be a high
probability that your Raspberry Pi Cameras are deployed in a remote, or generally
inaccessible, location.  Because of this, enabling the <span class="mono">watchdog</span> daemon
is recommended.  Watchdog can monitor and automatically reboot your Pi should it 
have become locked up without the need to power cycle it.  Assumming you have installed
the operating system version above, follow these steps to setup watchdog;

<br><br>Edit the system.conf file as a superuser using your favourite text editor.
In the example below, the <b>vi</b> editor is used;

<br><br>
<span class="mono"><span style="color:blue;">$ </span>sudo vi /etc/systemd/system.conf</span>
<br>

<dl>
 <dt>Uncomment line 29 and change to;</dt>
 <dd><span class="mono">RuntimeWatchdogSec=15</span></dd>
 <dt>Uncomment line 30;</dt>
 <dd><span class="mono">RebootWatchdogSec=10min</span></dd>
</dl>

Save the file, then issue;<br><br>

<span class="mono"><span style="color:blue;">$ </span>sudo systemctl daemon-reload</span>
<br>
<span class="mono"><span style="color:blue;">$ </span>sudo reboot</span>
<br><br>
Now, once your Pi reboots, the watchdog daemon should be configured correctly to monitor 
and automatically reboot your Pi should it become locked up.  To test this watchdog facility, 
refer to the <span class="mono">bomb.py</span> test program under 
<a href="#appendix-2">Appendix 2 - Auxiliary client programs</a> section below.


<h4>Configure config.ini</h4>

Under the home directory is a file called <span class="mono">config.ini</span>.  To setup your 
Raspberry Pi client,
you will need to edit and configure this file accordingly.  Use a text editor to make changes to 
this file.  The initial (unconfigured) <span class="mono">config.ini</span> is as follows;

<br><br>
 config.ini
 <pre class="terminal" >
[SERVER]
FULL_BASE_URL =
AUTH_KEY = 

[CAMERA]
NAME = 
IMAGE_SIZE_WIDTH = 640
IMAGE_SIZE_HEIGHT = 480
IMAGE_ROTATION = 0
# Image ROTATION possible values;
# 0: No rotation
# 1: 90 degrees clockwise
# 2: 180 degrees
# 3: 270 degrees (90 degrees counter-clockwise)
MOTION_FRAMES_PER_SECOND = 10
MOTION_RECORD_SECONDS = 7
MOTION_DIFF_FRAMES = 2
MOTION_DEFAULT_NOISE_THRESHOLD = 20
MOTION_DEFAULT_WIND_STOP = 20
DELAY = 60

[NETWORK]
INTERFACE = wlan0 </pre>

<br><br>
Make the following changes as required;
<ul>
 <li>[SERVER]
  <dl>
   <dt>FULL_BASE_URL *</dt>
   <dd>This is a mandatory required setting.  The value of the FULL_BASE_URL needs to be 
       the full accessible web path to
       where you have installed this application on the server.  That is, your server name 
       and server install directory name.  In fact, this should be set up the same as what you
       have configured on your server <span class="mono">config.ini</span>.  However, in this 
       case, different to
       what was set up on your server, the value does not need to be enclosed in double
       quotes.  For example, suppose your web server is called "mydomain123.au" and you 
       have installed the server component of this application under a public facing web 
       directory called "my_beach_house_cams", then this value would be; 
        https://mydomain123.au/my_beach_house_cams.
       FULL_BASE_URL values are referred to as a SYSTEM.  Multiple Raspberry Pi client cameras
       may use the same SYSTEM.
       <br><br>
       An example of a FULL_BASE_URL that represents an endpoint or install SYSTEM is;
       <br><br>
       FULL_BASE_URL = <span class="result">https://mydomain123.au/my_beach_house_cams</span>
       <br><br>
   </dd>
   <dt>AUTH_KEY *</dt>
   <dd>This is a mandatory required setting.  This should be the free text value of
       the authentication key that has been setup on the server endpoint that this
       camera will use.  Note; this is not the hash value - it needs to be the free
       text value.  Also, do not enclose the value in double quotes.
       <br><br>
       For example suppose you have decided on an AUTH KEY or password of;
        <span class="result">MySecretPassword0123</span>
       <br><br>
       Then the value to place here is;
       <br><br>
       AUTH_KEY = <span class="result">MySecretPassword0123</span>
       <br><br>
       <em>Note: Please use your own AUTH KEY / password and do not copy these examples.</em>
   </dd>
  </dl>
 </li>
 <li>[CAMERA]
  <dl>
   <dt>NAME</dt>
   <dd>This is an optional setting.
       This value will be used as the Camera (cam) Name.  However, it is optional.  If this
       value is not set, then the Raspberry Pi hostname will be used as the Camera Name.
       Note that for a given SYSTEM, Camera Names need to be unique for each Raspberry Pi
       camera.  If this value is provided, it will override the Raspberry Pi hostname for 
       the Camera Name.  Do not enclose this value in double quotes.
       <br><br>
       For example, this is a possible camera name;
       <br><br>
       NAME = <span class="result">Front Door</span>
       <br><br>
   </dd>
   <dt>IMAGE_SIZE_WIDTH &amp; IMAGE_SIZE_HEIGHT</dt>
   <dd>These values specify the capture image dimensions in pixels.  These values can
       be increased or decreased accordingly.  The script <span class="mono">resolutions.py</span>
       which is discussed further in <a href="#appendix-2">Appendix 2 - Auxiliary client programs</a> 
       can be used to list the available resolutions for the camera connected to your Pi.
       In most cases it is recommended to leave these values as the default setting (640 x 480).
       <br><br>
       <em>Note: Increasing these values too large, along with some other parameters here, may
        result in imagery filesizes that are too large and may be rejected by your, or any subscribers,
        email servers.</em>
       <br><br>
   </dd>
   <dt>IMAGE_ROTATION</dt>
   <dd>This is an optional setting.  Default value is 0.  Valid values are; 0, 1, 2 and 3.  This can 
       be used to rotate captured still and video imagery accordingly dependent upon how you have 
       oriented your Raspberry Pi Camera.
       <br><br>
   <dt>MOTION_FRAMES_PER_SECOND</dt>
   <dd>This is the frame rate (frames per second) video capture rate that will be requested of
       your camera.  The default value is 10.  This can be increased or decreased accordingly.
       <br><br>
       <em>Note: Increasing this value too large, along with some other parameters here, may
        result in imagery filesizes that are too large and may be rejected by your, or any subscribers,
        email servers.</em>
       <br><br>
   </dd>
   <dt>MOTION_RECORD_SECONDS</dt>
   <dd>This is the approximate duration in seconds a video will be recorded for a detected motion.
       This is an approximate time as the time of detecting the motion is also added to
       any recorded video.  This has a default value of 7 seconds.  This value can be increased
       or decreased accordingly.
       <br><br>
       <em>Note: Increasing this value too large, along with some other parameters here, may
        result in imagery filesizes that are too large and may be rejected by your, or any subscribers,
        email servers.</em>
       <br><br>
   <dt>MOTION_DIFF_FRAMES</dt>
   <dd>For motion to be detected, there must be this value + 1 consecutive frames
       from the image stream that differ by, at least, the Image Noise Threshold setting.
       This has a default value of 3 which means that there must be 4 consecutive frames
       differing by the minimum noise value setting for motion to be considered.  Generally,
       this value does not need to be altered.  And care should be taken when altering this
       value.  However, if your camera is experiencing false motion detection's from short
       transient flashes in light, you may wish to tune/adjust this value.  If necessary
       to change this value, It is recommended to do so in small increments.
       <br><br>
   <dt>MOTION_DEFAULT_NOISE_THRESHOLD</dt>
   <dd>This is the default motion detect noise setting the camera will initially be started at.
       As soon as any subscriber has set or updated this Camera configuration, this value will
       not be used.  It is simply the initial startup setting until a subscriber updates it
       by setting the configuration for this Camera.
       <br><br></dd>
   <dt>MOTION_DEFAULT_WIND_STOP</dt>
   <dd>This is the default motion detect wind or gust kmh stop setting.  If a BOM weather station
       has been defined, this will be the default "Wind Stop" setting.  This value is only used at
       initial startup by the Camera.  As soon as any subscriber has set or updated this Camera 
       configuration, this value will not be used.  It is simply the initial startup setting until 
       a subscriber updates it by setting the configuration for this Camera.
       <br><br></dd>
   <dt>DELAY</dt>
   <dd>This has a default value of 60.  This is the number of seconds the Camera will sleep after
       it has detected, and processed, an image motion.  This number can be increased or
       decreased accordingly.  However, decreasing the value too much may result in a high number
       of motion detect emails in a short time period should the Camera settings be overly 
       sensitive or generally poorly configured.  Generally, a subsequent motion detect email 
       will not be sent until this period of time has elapsed since a detected motion.
       <br><br></dd>
  </dl>
 </li>
 <li>[NETWORK]
  <dl>
   <dt>INTERFACE</dt>
   <dd>Currently, this is only used to assist the "Wifi scan" functionality.  The default value
       is wlan0.  If your Pi uses another network interface, then specify that here accordingly.
       In future releases, this INTERFACE value may also be used for the ping reboot facility.
   </dd>
  </dl>
 </li>
</ul>
</p>


<h4>Setup crontab</h4>

<p>
One of the final steps is to configure your Pi crontab entry.  If you are unfamiliar, cron
is the Linux job scheduler and a crontab file is provided in the code base.  
Issue the following commands;
<br><br>
<span class="mono"><span style="color:blue;">$ </span>chmod +x motion_restarter.sh</span>
<br>
<span class="mono"><span style="color:blue;">$ </span>crontab &lt; crontab.l</span>
<br><br>
The Pi crontab should now be as follows;
<br><br>
 crontab.l
 <pre class="terminal" >
*/5 * * * * /usr/bin/python fetchconfig.py &gt;/dev/null 2&gt;/dev/null &amp;
53 * * * * /usr/bin/python ping_reboot.py &gt;/dev/null 2&gt;/dev/null &amp;
0 6 * * 0 /usr/bin/sudo /sbin/reboot &gt;/dev/null 2&gt;/dev/null &amp;
@reboot $HOME/motion_restarter.sh &gt;/dev/null 2&gt;/dev/null &amp;</pre>
<br>
A line-by-line description of this crontab is;
<ol>
 <li>Every 5 minutes the Pi will request a camera configuration payload from the server.</li>
 <li>At 53 minutes past every hour, the Pi will perform a custom ping reboot.  Technically,
     this is not quite a ping reboot but rather a wget reboot - in that it requests a
     simple static file from the server.  Should that fetch request fail, the Pi will
     reboot.  This is to help safeguard when the Pi might be deployed in unreliable networks.
     If you do not want this functionality - remove this line.</li>
 <li>Every Sunday morning at 6am, the Pi will reboot.  If you do not want this functionality -
     remove this line.</li>
 <li>Upon boot of the Pi, the shell script 
     <span class="mono">motion_restarter.sh</span> will be called.  This, in turn,
     will invoke the python <span class="mono">motion.py</span> program - which does all the
     camera motion detection work.  Should the python <span class="mono">motion.py</span> crash
     for any reason, the <span class="mono">motion_restarter.sh</span> script will attempt to 
     restart it for a limited number of retry attempts.</li>
</ol>


<h4>Reboot and Finish</h4>
<p>
This concludes the general setup of a client Raspberry Pi configured for motion detection.
The final step is to reboot the Pi and then future control would be from the received emails
and web portal for this application.  Issue the following command;
<br><br>
<span class="mono"><span style="color:blue;">$ </span>sudo reboot</span>
<br><br>
</p>

<hr>


<h4>Appendix 1 - Manual Headless Setup</h4>
<p id="appendix-1">
This Appendix is provided to detail a manual headless setup for a Raspberry Pi.
Such a manual technique is fairly well documented on the internet however it is
perhaps less commonly used now with the introduction of the <b>Raspberry Pi Imager</b>.
However, there may be some cases where the Raspberry Pi Imager is perhaps not suited,
so it is described here.  Instructions here are very brief and you may need to consult
other resources if you require more detail.
<br><br>
Download a Raspberry Pi OS (Legacy) Lite image.
<br><br>
Eg; 2024-07-04-raspios-bullseye-armhf-lite.img.xz
<br><br>
You may need to uncompress this archive to work with a traditional Imager. 7-Zip can uncompress such .xz files.
<br><br>
You should now have; 2024-07-04-raspios-bullseye-armhf-lite.img
<br><br>
Use a traditional Imager (such as Win32DiskImager etc) to burn this image to your micro SD card
<br><br>
You now need to create 3 files and copy them to the root directory of your SD card;
<ol>
 <li><span class="mono">wpa_supplicant.conf</span> - <em>To Specify your Wifi credentials</em></li>
 <li><span class="mono">userconf.txt</span> - <em>To specify username and password</em></li>
 <li><span class="mono">ssh</span> - <em>An empty file to permit ssh connections/logins</em></li>
</ol>

<h5>wpa_supplicant.conf</h5>
This file needs to be created and will contain your Wifi credentials.  Note that the
<span class="mono">wpa_supplicant.conf</span> is applicable to Legacy OS versions.
Later OS versions do not use this file for Pi network configuration.  
Consider the following empty template of a sample <span class="mono">wpa_supplicant.conf</span>;

<pre class="terminal">
country=AU
ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1
network={
 ssid=""
 psk=""
 key_mgmt=WPA-PSK
}
network={
 key_mgmt=NONE
 priority=-999
}</pre>

Here the first <span class="mono">network</span> entry contains empty strings for <span class="mono">ssid</span>
 and <span class="mono">psk</span> - you need to populate
these with your Wifi SSID name and Wifi password respectively.  The second 
 <span class="mono">network</span> entry will
allow your Pi to connect to password-less Wifi networks should that be available - if you do not want 
that then remove that second <span class="mono">network</span> entry in its entirety.
<br><br>
For example; suppose your Wifi network SSID is <span style="color:blue">Batman</span> and your
Wifi network password is <span style="color:blue">Robin</span> and you <b>do not</b> wish your
Pi to connect to password-less Wifi networks, then your <span class="mono">wpa_supplicant.conf</span>
file would be as follows;
<pre class="terminal">
country=AU
ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1
network={
 ssid="Batman"
 psk="Robin"
 key_mgmt=WPA-PSK
}</pre>
Using a text editor, create an appropriate <span class="mono">wpa_supplicant.conf</span> file and
then copy that to the root directory of your SD card.

<h5>userconf.txt</h5>
This file needs to be created with credentials that will be used when you log in to your
Raspberry Pi using ssh.  This simply needs to be a 1 line file in the format of; 
<em>username</em><b>:</b><em>password hash</em>.  That is a username separated by a colon then
the password hash for that username.  Raspberry Pi OS traditionally use a username called 
<span class="mono"><b>pi</b></span>.
So, all that is needed is to generate a password hash.  You can generate a password
hash using the <a href="hash.php">Hash Generator</a> or using a Linux command if you
already have access to another Linux system.
<br><br>
Suppose you decide to use a password; <span class="result">MySecretPiPassword0123</span>, 
then by using the Hash Generator,
you if you enter that password, then a possible "Linux Crypt Hash" of that password is; 
 <span class="result">ZQ1hwVH78F09Q</span>.
<em>Note in this case, using the Hash Generator, use the "Linux Crypt Hash" value for this client setup
and do not use the "PHP Password Hash" (which is applicable to the server setup not client)</em>.  Then
if you did that, you would then create a <span class="mono">userconf.txt</span> file as follows;
<pre class="terminal">
pi:ZQ1hwVH78F09Q</pre>
Using a text editor, create an appropriate <span class="mono">userconf.txt</span> file and
then copy that to the root directory of your SD card.
<br><br>
Alternatively, if you already have access to another Linux system here is an example command you
could use to also generate a password hash;
<br><br>
<span class="mono"><span style="color:blue;">$ </span> echo 'MySecretPiPassword0123' | openssl passwd -stdin</span>
<br><br>
<em>Note: Please use your own password and do not copy these examples.</em>

<h5>ssh</h5>
In order to permit or enable ssh login access on your Pi, you need to create an empty file called
<span class="mono">ssh</span> without any filename extension and then copy that empty file to
your root directory of your SD card
<br><br>
This concludes the Headless build.  Once you have completed the above steps by burning the image and
copying these three files, you can then eject your SD card and insert it in to your Pi.  Power up
your Pi then wait 1 minute for it to boot, then attempt to ssh (login) to your Pi by connecting
to a host called <span class="mono"><b>raspberrypi</b></span> on your local Wifi network.  
</p>

<h4>Appendix 2 - Auxiliary client programs</h4>
<p id="appendix-2">
As part of your client build, there are some auxiliary python files included that are not used
in the normal camera motion detection operation.  Instead, these files are provided for assistance in
testing and checking various configurations.
<h5>bomb.py</h5>
This is a very simple python script that will endlessly fork subprocesses.  It is designed to
deliberately crash and lock up your Pi.  This script is provided to help you test the 
<span class="mono">watchdog</span> daemon if you have setup that facility as per instructions above.  
To test if <span class="mono">watchdog</span> is working correctly, simply run the 
<span class="mono">bomb.py</span>
script, wait for your Pi to crash, then wait another minute for it to reboot, then see if you
can log back in successfully.  If you can log back in without the need to power cycle your Pi,
this is an indicator that <span class="mono">watchdog</span> is probably configured and working correctly.
Eg;
<br><br>
<span class="mono"><span style="color:blue;">$ </span> python bomb.py >/dev/null 2>/dev/null &</span>

<h5>resolutions.py</h5>
This python script is provided to simply list the Camera resolution sizes available on your Pi.
This may be useful if you will be modifying the IMAGE_SIZE_WIDTH  and IMAGE_SIZE_HEIGHT
configuration parameters.  So you can be sure you are using a valid supported camera image size.
Eg, to list camera resolution sizes available on your Pi, run;
<br><br>
<span class="mono"><span style="color:blue;">$ </span> python resolutions.py</span>
<br><br>
<em>Note: you may need to make sure the motion detection is not running when executing 
<span class="mono">resoltuions.py</span> and temporarily disable your crontab entries.</em>
</p>

<?php page_bottom(true);  ?>
