<?php
/**
 * MySQL Compatibility wrapper to use mysql_* commands in PHP 5.5+
 * Inspired by Aziz S. Hussain <azizsaleh@gmail.com>
 * 
 * @author    Steve Kamerman <stevekamerman@gmail.com>
 * @license   GPLv3 <http://www.gnu.org/copyleft/gpl.html>
 * @link      https://github.com/kamermans/mysql-compat
 */
if (defined('MYSQL_ASSOC')) {
   return;
}

define('MYSQL_CLIENT_LONG_PASSWORD', 1);
define('MYSQL_CLIENT_FOUND_ROWS', 2);
define('MYSQL_CLIENT_LONG_FLAG', 4);
define('MYSQL_CLIENT_CONNECT_WITH_DB', 8);
define('MYSQL_CLIENT_NO_SCHEMA', 16);
define('MYSQL_CLIENT_COMPRESS', 32);
define('MYSQL_CLIENT_ODBC', 64);
define('MYSQL_CLIENT_LOCAL_FILES', 128);
define('MYSQL_CLIENT_IGNORE_SPACE', 256);
define('MYSQL_CLIENT_PROTOCOL', 512);
define('MYSQL_CLIENT_INTERACTIVE', 1024);
define('MYSQL_CLIENT_SSL', 2048);
define('MYSQL_CLIENT_IGNORE_SIGPIPE', 4096);
define('MYSQL_CLIENT_TRANSACTIONS', 8192);
define('MYSQL_CLIENT_RESERVED', 16384);
define('MYSQL_CLIENT_SECURE_CONNECTION', 32768);
define('MYSQL_CLIENT_MULTI_STATEMENTS', 65535);
define('MYSQL_CLIENT_MULTI_RESULTS', 131072);
define('MYSQL_CLIENT_REMEMBER_OPTIONS', 1 << 31);

define('MYSQL_ASSOC', 1);
define('MYSQL_NUM', 2);
define('MYSQL_BOTH', 3);