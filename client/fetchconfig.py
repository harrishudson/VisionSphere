import io, time, json, socket, os
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

DEBUG = False

def p(msg):
    if DEBUG:
        print(msg)

def fetch_config():
    try:
        url = BASE + "/fetchconfig_api.php"
        data = urllib.parse.urlencode({'AUTH_KEY' : AUTH_KEY,
                                       'CAM_LABEL' : CAM_LABEL})
        # For POST method use ...
        data_encoded = data.encode('utf-8')
        req = urllib.request.Request(url, data_encoded)
        # For GET method use ...
        #full_url = url + "?" + data
        #req = urllib.request.Request(full_url)
        response = urllib.request.urlopen(req)
        html = response.read()
        result = html.decode('utf-8')
        j = json.loads(result)
        return j
    except:
        return {}

def process_config(conf_json):

    sys_action = ''

    try:
        x = float(conf_json['score_threshold'])
        conf_json['score_threshold'] = x
    except:
        pass

    try:
        x = float(conf_json['wind_stop_kmh'])
        conf_json['wind_stop_kmh'] = x
    except:
        pass

    try:
        x = conf_json['system']
        sys_action = x
        conf_json['system'] = "None"
    except:
        pass

    p("sys_action=" + sys_action)

    write_config(conf_json)

    if (sys_action == "Reboot"):
        time.sleep(10)
        os.system('/usr/bin/sudo /sbin/reboot')

    if (sys_action == "Speed Test"):
        time.sleep(2)
        common.speed_test()

    if (sys_action == "Wifi Scan"):
        time.sleep(2)
        common.wifi_scan()

    if (sys_action == "Update"):
        try:
            url = BASE + "/client_software/motion.py"
            req = urllib.request.Request(url)
            response = urllib.request.urlopen(req)
            motion = open(FILE_BASE + '/motion.py', 'w')
            html = response.read()
            result = html.decode('utf-8')
            p("Writing software")
            motion.write(result)
            motion.close()
            time.sleep(2)
            os.system('/usr/bin/sudo /sbin/reboot');
        except:
            pass

def write_config(conf_json):
    j = json.dumps(conf_json)
    conf = open(FILE_BASE + '/config.dat', 'w')
    conf.write(j)
    conf.close()

# Main
conf = fetch_config()
p(conf)
process_config(conf)
