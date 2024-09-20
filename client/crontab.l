*/5 * * * * /usr/bin/python fetchconfig.py >/dev/null 2>/dev/null &
53 * * * * /usr/bin/python ping_reboot.py >/dev/null 2>/dev/null &
0 6 * * 0 /usr/bin/sudo /sbin/reboot >/dev/null 2>/dev/null &
@reboot $HOME/motion_restarter.sh >/dev/null 2>/dev/null &
