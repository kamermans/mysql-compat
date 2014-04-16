<?php
/**
 * MySQL Compatibility wrapper to use mysql_* commands in PHP 5.5+
 * Inspired by Aziz S. Hussain <azizsaleh@gmail.com>
 * 
 * @author    Steve Kamerman <stevekamerman@gmail.com>
 * @license   GPLv3 <http://www.gnu.org/copyleft/gpl.html>
 * @link      https://github.com/kamermans/mysql-compat
 */

use MySqlCompat\ConnectionManager;

if (function_exists('mysql_connect'))

if (!is_defined('MSYQL_COMPAT_WRAPPER')) {

	require_once __DIR__.'/../vendor/autoload.php';
	require_once __DIR__.'/definitions.php';
	
	// http://www.php.net/manual/en/function.mysql-connect.php
	function mysql_connect($server, $username, $password, $new_link = false, $client_flags = false) {
		return ConnectionManager::getInstance()->mysql_connect($server, $username, $password, $new_link, $client_flags);
	}
	
	// http://www.php.net/manual/en/function.mysql-connect.php
	function mysql_pconnect($server, $username, $password, $new_link = false, $client_flags = false) {
		return ConnectionManager::getInstance()->mysql_pconnect($server, $username, $password, $new_link, $client_flags);
	}
	
	// http://www.php.net/manual/en/function.mysql-select-db.php
	function mysql_select_db($database_name, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return $db->mysql_select_db($database_name);
	}
	function mysql_selectdb($database_name, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_select_db($database_name, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-query.php
	function mysql_query($query, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_query($query, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-real-escape-string.php
	function mysql_real_escape_string($string, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_real_escape_string($string, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-escape-string.php
	function mysql_escape_string($string, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_escape_string($string, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-fetch-array.php
	function mysql_fetch_array(&$result, $result_type = MYSQL_BOTH) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_fetch_array($result, $result_type);
	}
	
	// http://www.php.net/manual/en/function.mysql-fetch-assoc.php
	function mysql_fetch_assoc(&$result) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_fetch_assoc($result);
	}
	
	// http://www.php.net/manual/en/function.mysql-fetch-row.php
	function mysql_fetch_row(&$result) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_fetch_row($result);
	}
	
	// http://www.php.net/manual/en/function.mysql-fetch-object.php
	function mysql_fetch_object(&$result) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_fetch_object($result);
	}
	
	// http://www.php.net/manual/en/function.mysql-ping.php
	function mysql_ping($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_ping($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-errno.php
	function mysql_errno($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_errno($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-error.php
	function mysql_error($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_error($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-affected-rows.php
	function mysql_affected_rows($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_affected_rows($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-client-encoding.php
	function mysql_client_encoding($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_client_encoding($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-close.php
	function mysql_close($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_close($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-create-db.php
	function mysql_create_db($database_name, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_create_db($database_name, $link);
	}

	function  mysql_createdb($database_name, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_create_db($database_name, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-data-seek.php
	function mysql_data_seek($result, $row_number) {
		return ConnectionManager::getInstance()->mysql_data_seek($result, $row_number);
	}
	
	// http://www.php.net/manual/en/function.mysql-list-dbs.php
	function mysql_list_dbs($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_list_dbs($link);
	}
	function mysql_listdbs($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_list_dbs($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-db-name.php
	function mysql_db_name(&$result, $row, $field = 'Database') {

		return ConnectionManager::getInstance()->mysql_db_name($result, $row, $field);
	}
	function mysql_dbname(&$result, $row, $field = 'Database') {
		return ConnectionManager::getInstance()->mysql_db_name($result, $row, $field);
	}
	
	// http://www.php.net/manual/en/function.mysql-db-query.php
	function mysql_db_query($databaseName, $query, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_db_query($databaseName, $query, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-drop-db.php
	function mysql_drop_db($databaseName, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_drop_db($databaseName, $link);
	}
	function  mysql_dropdb($databaseName, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_drop_db($databaseName, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-unbuffered-query.php
	function mysql_unbuffered_query($query, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_unbuffered_query($query, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-thread-id.php
	function mysql_thread_id($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_thread_id($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-list-tables.php
	function mysql_list_tables($databaseName, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_list_tables($databaseName, $link);
	}
	function mysql_listtables($databaseName, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_list_tables($databaseName, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-tablename.php
	function mysql_tablename(&$result, $row) {
		return ConnectionManager::getInstance()->mysql_tablename($result, $row);
	}
	
	// http://www.php.net/manual/en/function.mysql-stat.php
	function mysql_stat($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_stat($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-set-charset.php
	function mysql_set_charset($charset, $link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_set_charset($charset, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-result.php
	function mysql_result(&$result, $rows, $field = false) {
		return ConnectionManager::getInstance()->mysql_result($result, $rows, $field);
	}
	
	// http://www.php.net/manual/en/function.mysql-list-processes.php
	function mysql_list_processes($link = false) {
		$db = $link? ConnectionManager::getInstance()->getConnection($link): ConnectionManager::getInstance()->defaultConnection;
		return ConnectionManager::getInstance()->mysql_list_processes($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-insert-id.php
	function mysql_insert_id($link = false) {
		return ConnectionManager::getInstance()->mysql_insert_id($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-get-server-info.php
	function mysql_get_server_info($link = false) {
		return ConnectionManager::getInstance()->mysql_get_server_info($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-get-proto-info.php
	function mysql_get_proto_info($link = false) {
		return ConnectionManager::getInstance()->mysql_get_proto_info($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-get-host-info.php
	function mysql_get_host_info($link = false) {
		return ConnectionManager::getInstance()->mysql_get_host_info($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-get-client-info.php
	function mysql_get_client_info($link = false) {
		return ConnectionManager::getInstance()->mysql_get_client_info($link);
	}
	
	// http://www.php.net/manual/en/function.mysql-free-result.php
	function mysql_free_result(&$result) {
		return ConnectionManager::getInstance()->mysql_free_result($result);
	}
	function  mysql_freeresult(&$result) {
		return ConnectionManager::getInstance()->mysql_free_result($result);
	}
	
	// http://www.php.net/manual/en/function.mysql-fetch-lengths.php
	function mysql_fetch_lengths(&$result) {
		return ConnectionManager::getInstance()->mysql_fetch_lengths($result);
	}
	
	// http://www.php.net/manual/en/function.mysql-list-fields.php
	function mysql_list_fields($databaseName, $tableName, $link = false) {
		return ConnectionManager::getInstance()->mysql_list_fields($databaseName, $tableName, $link);
	}
	function mysql_listfields($databaseName, $tableName, $link = false) {
		return ConnectionManager::getInstance()->mysql_list_fields($databaseName, $tableName, $link);
	}
	
	// http://www.php.net/manual/en/function.mysql-field-len.php
	function mysql_field_len(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_len($result, $fieldOffset);
	}
	function  mysql_fieldlen(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_len($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-field-flags.php
	function mysql_field_flags(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_flags($result, $fieldOffset);
	}
	function  mysql_fieldflags(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_flags($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-field-name.php
	function mysql_field_name(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_name($result, $fieldOffset);
	}
	function  mysql_fieldname(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_name($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-field-type.php
	function mysql_field_type(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_type($result, $fieldOffset);
	}
	function  mysql_fieldtype(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_type($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-field-table.php
	function mysql_field_table(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_table($result, $fieldOffset);
	}
	function mysql_fieldtable(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_table($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-field-seek.php
	function mysql_field_seek(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_field_seek($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-fetch-field.php
	function mysql_fetch_field(&$result, $fieldOffset = false) {
		return ConnectionManager::getInstance()->mysql_fetch_field($result, $fieldOffset);
	}
	
	// http://www.php.net/manual/en/function.mysql-num-fields.php
	function mysql_num_fields($result) {
		return ConnectionManager::getInstance()->mysql_num_fields($result);
	}
	function mysql_numfields($result) {
		return ConnectionManager::getInstance()->mysql_num_fields($result);
	}
	
	// http://www.php.net/manual/en/function.mysql-num-rows.php
	function mysql_num_rows($result) {
		return ConnectionManager::getInstance()->mysql_num_rows($result);
	}
	function mysql_numrows($result) {
		return ConnectionManager::getInstance()->mysql_num_rows($result);
	}
	
	// http://php.net/manual/en/function.mysql-info.php
	function mysql_info($link = false) {
		return ConnectionManager::getInstance()->mysql_info($link);
	}
	
	// Close all connections
	function mysql_close_all() {
		return ConnectionManager::getInstance()->mysql_close_all();
	}
	
	// is_resource function over ride
	function is_resource_custom($resource) {
		return ConnectionManager::getInstance()->is_resource($resource);
	}
	
	// get_resource_type_custom function over ride
	function get_resource_type_custom($resource) {
		return ConnectionManager::getInstance()->get_resource_type($resource);
	}

	define('MSYQL_COMPAT_WRAPPER', 1);
}