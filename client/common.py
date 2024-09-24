import configparser
import io, time, json, socket, os, random
import urllib, urllib.parse, urllib.request

config = configparser.ConfigParser()
config.read('config.ini')

DEBUG = False

def p(msg):
    if DEBUG:
        print(msg)

def get_server_full_base_url():
    return config['SERVER']['FULL_BASE_URL']

def get_server_auth_key():
    return config['SERVER']['AUTH_KEY']

def get_camera_name():
    name1 = config['CAMERA']['NAME']
    if (name1):
        return name1
    return socket.gethostname()

def get_camera_image_size():
    w = int(config['CAMERA']['IMAGE_SIZE_WIDTH'])
    h = int(config['CAMERA']['IMAGE_SIZE_HEIGHT'])
    return {"width": w, "height": h}

def get_camera_image_rotation():
    return int(config['CAMERA']['IMAGE_ROTATION'])

def get_camera_motion_diff_frames():
    return int(config['CAMERA']['MOTION_DIFF_FRAMES'])

def get_camera_motion_frames_per_second():
    return int(config['CAMERA']['MOTION_FRAMES_PER_SECOND'])

def get_camera_motion_record_seconds():
    return int(config['CAMERA']['MOTION_RECORD_SECONDS'])

def get_camera_motion_default_noise_threshold():
    return int(config['CAMERA']['MOTION_DEFAULT_NOISE_THRESHOLD'])

def get_camera_motion_default_wind_stop():
    return int(config['CAMERA']['MOTION_DEFAULT_WIND_STOP'])

def get_camera_delay():
    return int(config['CAMERA']['DELAY'])

def get_network_interface():
    return config['NETWORK']['INTERFACE']

def precheck():
    try:
        BASE = get_server_full_base_url()
        if not BASE:
            return False
        url = BASE + "/precheck_api.php"
        AUTH_KEY = get_server_auth_key()
        if not AUTH_KEY:
            return False
        data = urllib.parse.urlencode({'AUTH_KEY' : AUTH_KEY})
        # For POST method use ...
        data_encoded = data.encode('utf-8')
        req = urllib.request.Request(url, data_encoded)
        # For GET method
        #full_url = url + "?" + data
        #req = urllib.request.Request(full_url)
        html = urllib.request.urlopen(req).read()
        result = html.decode('utf-8')
        if not result:
            return False
        j = json.loads(result)
        if not j:
            return False
        if (j['subscribers'] == "no"):
            return False
        return True
    except:
        return False

def logger(action, file_contents):
    try:
        BASE = get_server_full_base_url()
        if not BASE:
            return False
        AUTH_KEY = get_server_auth_key()
        if not AUTH_KEY:
            return False
        CAM_LABEL = get_camera_name()
        if not CAM_LABEL:
            return False
        url = BASE + "/logger_api.php"
        data = urllib.parse.urlencode({'AUTH_KEY' : AUTH_KEY,
                                       'ACTION' : action,
                                       'CAM_LABEL' : CAM_LABEL,
                                       'FILE' : file_contents })
        data_encoded = data.encode('utf-8')
        # For POST method use ...
        req = urllib.request.Request(url, data_encoded)
        # For GET method
        # full_url = url + "?" + data
        #req = urllib.request.Request(full_url)
        response = urllib.request.urlopen(req).read()
        p(response)
    except:
        pass

def speed_test():
    rand = str(random.randint(1,10000))
    outfile = f"/var/tmp/speedtest_{rand}.out"
    os.system(f"speedtest-cli 2>&1 >{outfile}")
    fileptr = open(outfile, 'r')
    f_contents = fileptr.read()
    logger('Speed Test', f_contents)
    os.system(f"rm -f {outfile} & >/dev/null 2>/dev/null")

def wifi_scan():
    rand = str(random.randint(1,10000))
    outfile = f"/var/tmp/wifi_scan_{rand}.out"
    os.system(f"rm -f {outfile} >/dev/null 2>/dev/null")
    cmd = '/usr/bin/sudo iw dev ' + get_network_interface() + ' scan'
    os.system(f"{cmd} 2>&1 >{outfile}")
    fileptr = open(outfile, 'r')
    f_contents = fileptr.read()
    logger('Wifi Scan', f_contents)
    os.system(f"rm -f {outfile} & >/dev/null 2>/dev/null")

def ping_reboot():
    BASE = get_server_full_base_url()
    if not BASE:
        return False
    url = BASE + "/ping.txt"
    req = urllib.request.Request(url)
    try:
        html = urllib.request.urlopen(req).read()
        result = html.decode('utf-8')
        if not result:
            os.system('/usr/bin/sudo /sbin/reboot &')
    except:
        os.system('/usr/bin/sudo /sbin/reboot &')

