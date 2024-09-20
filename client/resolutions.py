# Script to show rPi camera resolutions

from picamera2 import Picamera2

PICAM = Picamera2()

PICAM.configure(PICAM.create_still_configuration())

camera_info = PICAM.sensor_modes

# Show available resolutions
for mode in camera_info:
    resolution = mode["size"]
    print(f"Resolution: {resolution[0]} x {resolution[1]}")

