*/5 * * * * /usr/bin/python $HOME/fetchconfig.py </dev/null >/dev/null 2>/dev/null &
53 * * * * /usr/bin/python $HOME/ping_reboot.py </dev/null >/dev/null 2>/dev/null &
0 6 * * 0 /usr/bin/sudo /sbin/reboot >/dev/null 2>/dev/null &
@reboot nohup $HOME/motion_restarter.sh </dev/null >/dev/null 2>/dev/null &
