#!/bin/bash
ATTEMPTS=0
FULL_LOG=${HOME}/motion_restarter_full_error.log
TRIMMED_LOG=${HOME}/motion_restarter_trimmed_error.log
while [ $ATTEMPTS -le 5 ]
 do
  /usr/bin/sleep 10 
  ATTEMPTS=$[$ATTEMPTS+1]
  >${FULL_LOG}
  >${TRIMMED_LOG}
  /usr/bin/python ${HOME}/motion.py </dev/null >/dev/null 2>${FULL_LOG}
  /usr/bin/sleep 2
  /usr/bin/tail -100 ${FULL_LOG} >${TRIMMED_LOG} 2>/dev/null
  /usr/bin/python ${HOME}/motion_logger.py <${TRIMMED_LOG} >/dev/null 2>/dev/null
 done
