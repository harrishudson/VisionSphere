#!/usr/bin/python3

import os, glob, io, subprocess, datetime, time, json, random, base64, math 
import numpy as np
from picamera2 import Picamera2
from picamera2.encoders import H264Encoder
from picamera2.outputs import FileOutput
from PIL import Image
import urllib, urllib.parse, urllib.request
import common

FILE_BASE = os.path.dirname(os.path.realpath(__file__))

BASE = common.get_server_full_base_url()
if not BASE:
    exit()
AUTH_KEY = common.get_server_auth_key()
if not AUTH_KEY:
    exit()
CAM_LABEL = common.get_camera_name()
if not CAM_LABEL:
    exit()

RESOLUTION = common.get_camera_image_size()
IMAGE_ROTATION = common.get_camera_image_rotation()
VSIZE = (int(RESOLUTION['width']), int(RESOLUTION['height']))
# Transpose size if image rotated either direction by 90 degrees
if ((IMAGE_ROTATION == 1) or (IMAGE_ROTATION == 3)):
    VSIZE = (int(RESOLUTION['height']), int(RESOLUTION['width']))
FRAMES_PER_SECOND = int(common.get_camera_motion_frames_per_second())
RECORD_SECONDS = int(common.get_camera_motion_record_seconds())
SCORE_THRESHOLD = float(common.get_camera_motion_default_noise_threshold())
STOP_WIND_KMH = float(common.get_camera_motion_default_wind_stop())
DELAY = int(common.get_camera_delay())
SINCE_LAST_CONFIG_POLL = ''
STATUS = 'Start'
BOM_ID = ''
BOM_WMO = ''
WEATHER_OBS = {}
DEBUG = False

def p(msg):
    if DEBUG:
        print(msg)

def send_email(img, img_type, timestamp, action, score):
    if (img != None):
        enc_img = base64.b64encode(img).decode('utf-8')
    else:
        enc_img = ''

    temp = 'Unknown'
    try:
        temp = os.popen("vcgencmd measure_temp | /bin/sed 's/temp=//' ").readline()
    except:
        temp = 'Unknown'

    try:
        uptime = os.popen("/usr/bin/uptime | /usr/bin/awk -F, '{print $1}'").readline()
    except:
        uptime = 'Unknown'

    url = BASE + '/sendit_api.php'
    try:
        data = urllib.parse.urlencode({'ACTION': action, 
                                       'PHOTO' : enc_img, 
                                       'IMAGE_TYPE' : img_type, 
                                       'TIMESTAMP' : timestamp, 
                                       'CAM_LABEL': CAM_LABEL, 
                                       'SCORE_THRESHOLD': SCORE_THRESHOLD, 
                                       'DELAY': DELAY, 
                                       'SINCE_LAST_CONFIG_POLL': SINCE_LAST_CONFIG_POLL, 
                                       'AUTH_KEY' : AUTH_KEY, 
                                       'SCORE': score, 
                                       'TEMP': temp, 
                                       'UPTIME': uptime, 
                                       'BOM_ID': BOM_ID, 
                                       'BOM_WMO': BOM_WMO, 
                                       'STOP_WIND_KMH': STOP_WIND_KMH, 
                                       'WEATHER_OBS': json.dumps(WEATHER_OBS) })


        # POST data
        data_encoded = data.encode('utf-8')
        req = urllib.request.Request(url, data_encoded)
        html = urllib.request.urlopen(req).read()
        result = html.decode('utf-8')
        p(result)
    except:
        p('Post email failure')

def read_config(read_only):
    global STATUS
    global SINCE_LAST_CONFIG_POLL
    global SCORE_THRESHOLD
    global DELAY
    global SINCE_LAST_CONFIG_POLL
    global BOM_ID
    global BOM_WMO
    global STOP_WIND_KMH
    global WEATHER_OBS

    try:
        stat = os.stat(FILE_BASE + '/config.dat');
        SINCE_LAST_CONFIG_POLL = datetime.datetime.fromtimestamp(stat.st_mtime)
        #p(SINCE_LAST_CONFIG_POLL)
        conf = open(FILE_BASE + '/config.dat', 'r')
        #conf_json = json.load(conf.read());
        conf_json = json.load(conf);
    except:
        p('read_config - failed to read config')
        return

    p('Read Config ...')
    p(conf_json)

    try:
        x = float(conf_json['score_threshold'])
        SCORE_THRESHOLD = x
        conf_json['score_threshold'] = x
        p(f"SCORE_THRESHOLD = {SCORE_THRESHOLD}")
    except:
        pass

    try:
        WEATHER_OBS = conf_json['weather_obs']
        p('Weather obs ...')
        p(WEATHER_OBS)
    except:
        pass

    try:
        BOM_ID = conf_json['bom_id']
        BOM_WMO = conf_json['bom_wmo']
        STOP_WIND_KMH = float(conf_json['stop_wind_kmh'])
    except:
        #STOP_WIND_KMH = 999
        pass

    if read_only:
        p('Read Config Only Completed')
        return 

    NEW_STATUS = ''

    try:
        x = conf_json['status']
        if ((x == 'Start') or (x == 'Stop') or (x == 'Take Photo') or (x == 'Take Recording')):
            NEW_STATUS = x
        conf_json['status'] = 'None'
    except:
        pass

    if (NEW_STATUS == 'Start'):
        STATUS = 'Start'
        write_config(conf_json)
        send_email(None, None, None, 'Start', None)

    if (NEW_STATUS == 'Stop'):
        STATUS = 'Stop'
        write_config(conf_json)
        send_email(None, None, None, 'Stop', None)

    if (NEW_STATUS == 'Take Photo'):
        STATUS = 'Start'
        write_config(conf_json)
        take_photo('Photo Requested')

    if (NEW_STATUS == 'Take Recording'):
        STATUS = 'Start'
        write_config(conf_json)
        take_recording()

    p('read_config - Successfully Completed')

def write_config(conf_json):
    try:
        j = json.dumps(conf_json)
        conf = open(FILE_BASE + '/config.dat', 'w')
        conf.write(j)
        conf.close()
    finally:
        pass

def take_photo(msg):
    try:
        if common.precheck():
            frame = PICAM.capture_array('main')
            # Use if red and blue swapped (eg, RGB888 format)
            image = Image.fromarray(frame[..., ::-1], mode='RGB')
            # Use if red and blue okay
            # image = Image.fromarray(frame)
            rotated_image = image
            if (IMAGE_ROTATION == 1):
                # 90 clockwise 
                rotated_image = image.rotate(270, expand=True)  
                original_width, original_height = image.size
                rotated_image = rotated_image.resize((original_height, original_width))
            if (IMAGE_ROTATION == 2):
                # 180
                rotated_image = image.rotate(180)
            if (IMAGE_ROTATION == 3):
                # 90 counterclockwise 
                rotated_image = image.rotate(90, expand=True)  
                original_width, original_height = image.size
                rotated_image = rotated_image.resize((original_height, original_width))
            image_stream = io.BytesIO()
            rotated_image.save(image_stream, format='JPEG')
            send_email(image_stream.getvalue(), 'jpg', get_timestamp(), msg, 'N/A');
        else:
            p('Photo Not Sent - no active subscribers')
    finally:
        pass

def take_recording():
    try:
        if common.precheck():
            timestamp = get_timestamp()
            try:
                PICAM.stop_encoder()
            except:
                pass
            p('Reseting video stream')
            vrand = str(random.randint(1,10000))
            video_file = f"motion-{vrand}.h264"
            if os.path.isfile(video_file):
                os.remove(video_file)
            output_file = FileOutput(video_file) 
            p('Starting Recording')
            ENCODER.output = output_file
            PICAM.start_encoder(ENCODER)
            time.sleep(RECORD_SECONDS)
            PICAM.stop_encoder()
            p('Begin Converting Video')
            gif_rand = str(random.randint(1,10000))
            gif_file = f"motion-{gif_rand}.gif"
            if os.path.isfile(gif_file):
                os.remove(gif_file)
            convert_video(video_file, gif_file, FRAMES_PER_SECOND)
            p('Finished Converting Video')
            fileptr = open(gif_file, 'rb')
            img = fileptr.read()
            p('Sending video')
            send_email(img, 'gif', timestamp, 'Recording Requested', 'N/A')
            if os.path.isfile(video_file):
                os.remove(video_file)
            if os.path.isfile(gif_file):
                os.remove(gif_file)
        else:
            p('Recording Not Sent - no active subscribers')

    finally:
        pass

def wind_stop():
    try:
        a = int(WEATHER_OBS['wind_spd_kmh'])
        b = int(WEATHER_OBS['gust_kmh'])
    except:
        return(False)

    try:
        if ((a >= STOP_WIND_KMH) or (b >= STOP_WIND_KMH)):
            p('Wind Stopped')
            return(True)
        return(False)
    except:
        return(False)

def get_timestamp():
    return os.popen("/bin/date").readline()

def convert_video(input_file, output_file, framerate):
    rot = ""
    if (IMAGE_ROTATION == 1):
        # 90 clockwise
        rot = ",transpose=1"
    if (IMAGE_ROTATION == 2):
        # 180  (2 x 90 counterclockwise)
        rot = ",transpose=2,transpose=2"
    if (IMAGE_ROTATION == 3):
        # 90 counterclockwise 
        rot = ",transpose=2"

    fps = str(framerate)

    # Define the FFmpeg command
    command = [
        'ffmpeg',
        '-i', input_file,          # Input file
        '-filter_complex', f"setpts=N/({fps}*TB){rot}",
        '-c:v', 'gif',
        '-y', output_file ]

    # Run the command
    try:
        res = subprocess.run(command, stderr=subprocess.PIPE, text=True)
        if (not res):
            common.logger('Ffmpeg Video Conversion Failure.', res.stderr);
        p(f"Conversion to {output_file} completed successfully.")
    except subprocess.CalledProcessError as e:
        common.logger('Ffmpeg Video Conversion Failure.', res.stderr);
        p(f"Video conversion Error occurred: {e}")

def decimate_image(image_buffer, sf):
    # Convert buffer to a PIL Image
    image = Image.fromarray(image_buffer)
    # Resize the image (downsample)
    new_size = (int(image.width * sf), int(image.height * sf))
    # Possible fine tuning options (speed vs quality for other options)
    # downsampled_image = image.resize(new_size, Image.ANTIALIAS)
    # downsampled_image = image.resize(new_size, Image.NEAREST)
    # downsampled_image = image.resize(new_size, Image.BICUBIC)
    downsampled_image = image.resize(new_size)
    # Convert the downsampled image back to a numpy array for further processing
    downsampled_image_array = np.array(downsampled_image)
    return downsampled_image_array


# Main
# ----

# Cleanup video files
#os.system('rm -f motion-*.h264 motion-*.gif & >/dev/null 2>/dev/null')
for file_path in glob.glob("motion-*.h264"):
    try:
        os.remove(file_path)
    except OSError as e:
        pass
for file_path in glob.glob("motion-*.gif"):
    try:
        os.remove(file_path)
    except OSError as e:
        pass

PICAM = Picamera2()

video_config = PICAM.create_video_configuration(main={"size": VSIZE, "format": "RGB888"})
video_config['controls']['FrameRate'] = FRAMES_PER_SECOND 
PICAM.configure(video_config)
ENCODER = H264Encoder()
PICAM.start()

read_config(True)

time.sleep(20)
take_photo('Start')

w, h = VSIZE
prev = None
latest = None
encoding = False
confirmed_movement = False
ltime = 0
ctime = time.time()

vrand = str(random.randint(1,10000))
video_file = f"motion-{vrand}.h264"
if os.path.isfile(video_file):
    os.remove(video_file)
output_file = FileOutput(video_file) 

# Compute scale factor for image frames to be compared
# Use 480px as a target width or height
dim = max(w,h)
if (dim > 480):
    scale_factor = 480 / dim 
else:
    scale_factor = 1


# Main motion detection loop
# --------------------------

while True:
    if (not encoding):
        current_frame = PICAM.capture_array('main')
        if (scale_factor != 1):
            cur = decimate_image(current_frame, scale_factor)
        else:
            cur = current_frame
    if prev is not None:
        # Measure pixels differences between current and previous frame
        if (not encoding):
            confirmed_movement = False
            mse0 = np.square(np.subtract(cur, prev)).mean()
            p(f"Current/Previous frame diff score: {mse0}")
            p(f"Trigger score = {mse0}")
    
            if (mse0 > SCORE_THRESHOLD) and (not wind_stop()) and (STATUS != 'Stop'):
                # Possible motion in last 2 frames.  
                # Start encoding and check further.
                encoding = True
                ltime = time.time()
                timestamp = get_timestamp()
                ENCODER.output = output_file
                PICAM.start_encoder(ENCODER)
                encoding = True

                p('Possible motion in last 2 frames.')

                # Take a third frame
                latest_frame = PICAM.capture_array('main')
                if (scale_factor != 1):
                    latest = decimate_image(latest_frame, scale_factor)
                else:
                    latest = latest_frame
                mse1 = np.square(np.subtract(prev, latest)).mean()
                mse2 = np.square(np.subtract(cur, latest)).mean()
                if ((mse1 + mse2) > SCORE_THRESHOLD*2):
                    # Differences deteccted in the last 3 frames
                    # Movement has been confirmed
                    p('New Motion Detected in last 3 frames. ')
                    p('Motion confirmed.')
                    confirmed_movement = True
                else:
                    # Motion not confirmed by 3rd frame
                    # Abandon recording
                    p('Abandoning recording.  Reseting video stream')
                    PICAM.stop_encoder()
                    if os.path.isfile(video_file):
                        os.remove(video_file)
                    vrand = str(random.randint(1,10000))
                    video_file = f"motion-{vrand}.h264"
                    if os.path.isfile(video_file):
                        os.remove(video_file)
                    output_file = FileOutput(video_file) 
                    ENCODER.output = output_file
                    encoding = False
                    confirmed_movement = False
                    prev = cur
                    cur = latest

    if (encoding) and (confirmed_movement) and (encoding and time.time() - ltime > RECORD_SECONDS):
        PICAM.stop_encoder()
        encoding = False
        cur = None
        prev = None
        p('Stopped Recording')
        if common.precheck():
            p('Begin Converting Video')
            gif_rand = str(random.randint(1,10000))
            gif_file = f"motion-{gif_rand}.gif"
            if os.path.isfile(gif_file):
                os.remove(gif_file)
            convert_video(video_file, gif_file, FRAMES_PER_SECOND)
            p('Finished Converting Video')
            p(gif_file)
            fileptr = open(gif_file, 'rb')
            img = fileptr.read()
            p('Sending video')
            send_email(img, 'gif', timestamp, 'Motion Detected', round(mse0,2))
            if os.path.isfile(gif_file):
                os.remove(gif_file)
            img = None
        else:
            p('No active subscribers - precheck failure')

        p('Reseting video stream')
        if os.path.isfile(video_file):
            os.remove(video_file)
        vrand = str(random.randint(1,10000))
        video_file = f"motion-{vrand}.h264"
        if os.path.isfile(video_file):
            os.remove(video_file)
        output_file = FileOutput(video_file) 
        ENCODER.output = output_file

        p('Sleeping')
        time.sleep(DELAY)
        p('Resuming')
        read_config(True)
              
    prev = cur
    
    # If not currenctly recording check config after every 20 seconds
    if (not encoding):
        if (time.time() - ctime > 20):
            read_config(False)
            ctime = time.time()
