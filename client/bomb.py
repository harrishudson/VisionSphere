#/bin/python
# bomb.py - Script to test rPi watchdog facility
# If watchdog is correctly setup, rPi should reboot after running this script

import os
while True:
      os.fork()

# To run from command line;
# python bomb.py >/dev/null 2>/dev/null &
