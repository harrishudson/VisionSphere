#!/bin/bash
ATTEMPTS=0
FULL_LOG=motion_restarter_full_error.log
TRIMMED_LOG=motion_restarter_trimmed_error.log
while [ $ATTEMPTS -le 5 ]
 do
  sleep 10 
  ATTEMPTS=$[$ATTEMPTS+1]
  >${FULL_LOG}
  >${TRIMMED_LOG}
  python motion.py >/dev/null 2>${FULL_LOG}
  sleep 2
  /usr/bin/tail -100 ${FULL_LOG} >${TRIMMED_LOG} 2>/dev/null
  cat ${TRIMMED_LOG} | python motion_logger.py >/dev/null 2>/dev/null
 done
