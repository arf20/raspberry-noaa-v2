#!/bin/bash
#
# Purpose: Upload images to FTP server
#
# Instructions:
#  1. Edit settings.yml to configure your FTP server, user and password.
# 
# Input parameters:
#   1. FTP server host
#   2. username
#   3. password
#   4. image file path
#
# Example:
#   ./scripts/push_processors/push_ftp.sh test.com user 1234 /srv/images/NOAA-18-20210212-091356-MCIR.jpg

# import common lib and settings
. "$HOME/.noaa-v2.conf"
. "$NOAA_HOME/scripts/common.sh"

# input params
SERVER=$1
USER=$2
PASSWD=$3
FILE=$4

# check that the file exists and is accessible
if [ -f "${FILE}" ]; then 
  log "Uploading $FILE to FTP $SERVER" "INFO"
  email_log=$(curl --upload-file ${FILE} ftp://${USER}:${PASSWD}@${SERVER}/ 2>&1)
  log "${email_log}" "INFO"
else
  log "Could not find or access image/attachment - not uploading to FTP server" "ERROR"
fi
