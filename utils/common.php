<?php
/* Copyright (c) 2011 Synology Inc. All rights reserved. */
define('ERR_UNKNOWN', 1);
define('ERR_FILEHOST_EXIST', 2);
define('ERR_INVALID_FILEHOST', 3);
define('LOGIN_FAIL', 4);
define('USER_IS_FREE', 5);
define('USER_IS_PREMIUM', 6);
define('ERR_UPATE_FAIL', 7);
define('ERR_FILE_NO_EXIST', 114);
define('ERR_REQUIRED_PREMIUM', 115);
define('ERR_NOT_SUPPORT_TYPE', 116);
define('ERR_REQUIRED_ACCOUNT', 124);
define('ERR_TRY_IT_LATER', 125);
define('ERR_TASK_ENCRYPTION', 126);
define('ERR_MISSING_PYTHON', 127);
define('ERR_PRIVATE_VIDEO', 128);
//define('DEFAULT_HOST_DIR', dirname(realpath($argv[0])) . "/" . 'hosts');
define('USER_HOST_DIR', '/var/packages/DownloadStation/etc/download/userhosts');
define('USER_HOST_CONF_DIR', '/var/packages/DownloadStation/etc/download/host.conf');
define('WGET', '/var/packages/DownloadStation/target/bin/wget');
define(
    'DOWNLOAD_STATION_USER_AGENT',
    "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535 (KHTML, like Gecko) Chrome/14 Safari/535"
);
define('DOWNLOAD_TIMEOUT', 20);
define('DOWNLOAD_URL', 'downloadurl');
define('DOWNLOAD_FILENAME', 'filename');
define('DOWNLOAD_COUNT', 'count');
define('GET_DOWNLOAD_INFO', 'getdownloadinfo');
define('GET_FILELIST', 'getfilelist');
//-1: use input url query again, but schedule don't input waiting host name to php.
//0: don't query again
//1: use input url query again,
//2: use parse url query again
define('DOWNLOAD_ISQUERYAGAIN', 'isqueryagain');
define('DOWNLOAD_ISPARALLELDOWNLOAD', 'isparalleldownload');
define('DOWNLOAD_ERROR', 'error');
define('DOWNLOAD_COOKIE', 'cookiepath');
define('DOWNLOAD_USERNAME', 'username');
define('DOWNLOAD_PASSWORD', 'password');
define('DOWNLOAD_ENABLE', 'enable');
define('DOWNLOAD_CONTINUE', 'continue');
define('DOWNLOAD_EXTRAINFO', 'extrainfo');
define('DOWNLOAD_LIST_NAME', 'list_name');
define('DOWNLOAD_LIST_FILES', 'list_files');
define('DOWNLOAD_LIST_SELECTED', 'list_selected');
define('INFO_NAME', 'name');
define('INFO_HOST_PREFIX', 'hostprefix');
define('INFO_DISPLAY_NAME', 'displayname');
define('INFO_VERSION', 'version');
define('INFO_AUTHENTICATION', 'authentication');
define('INFO_ISDOWNLOADER', 'isdownloader');
define('INFO_MODULE', 'module');
define('INFO_CLASS', 'class');
define('INFO_DESCRIPTION', 'description');
define('INFO_SUPPORTLIST', 'supporttasklist');
define('CURL_OPTION_SAVECOOKIEFILE', 'SaveCookieFile');
define('CURL_OPTION_LOADCOOKIEFILE', 'LoadCookieFile');
define('CURL_OPTION_POSTDATA', 'PostData');
define('CURL_OPTION_COOKIE', 'Cookie');
define('CURL_OPTION_HTTPHEADER', 'HttpHeader');
define('CURL_OPTION_FOLLOWLOCATION', 'FollowLocation');
define('CURL_OPTION_HEADER', 'Header');
