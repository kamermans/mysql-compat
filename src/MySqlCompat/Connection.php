<?php 

namespace MySqlCompat;

/**
 * MySQL Compatibility wrapper to use mysql_* commands in PHP 5.5+
 * Inspired by Aziz S. Hussain <azizsaleh@gmail.com>
 * 
 * @author    Steve Kamerman <stevekamerman@gmail.com>
 * @license   GPLv3 <http://www.gnu.org/copyleft/gpl.html>
 * @link      https://github.com/kamermans/mysql-compat
 */

use PDO;
use PDOStatement;
use Exception;

class Connection {

	/**
	 * @var pdo
	 */
	protected $pdo;

	protected $resource;
	protected $signature;
	protected $params = array();
	
	/**
	 * Next offset used by mysql_field_seek
	 *
	 * @var int
	 */
	protected $next_offset = false;
	
	/**
	 * Row seek
	 *
	 * @var int
	 */
	protected $row_seek = array();

	public function __construct($host, $username, $password, $persistent=false, $clientFlags = false) {}
		$this->signature = self::createSignature($host, $username, $password, $clientFlags);
		$flags = $this->flagsToDriverOptions($clientFlags);

		if ($persistent) {
			$flags[PDO::ATTR_PERSISTENT] = true;
		}
		
		// Set connection params
		$this->params = array (
			'server'        => $host,
			'username'      => $username,
			'password'      => $password,
			'newLink'       => $newLink,
			'clientFlags'   => $clientFlags,
			'errno'         => 0,
			'error'         => "",
			'rowCount'      => -1,
			'lastQuery'     => false,
		);

		// Create new instance
		$dsn = "mysql:host={$host}";
		try {
			// Add instance
			$this->pdo = new PDO($dsn, $username, $password, $flags);

			return $usePosition;
		} catch (PDOException $e) {
			// Mock the instance for error reporting
			$this->loadError($e);
			return false;
		}
		
		return false;
	}

	public function __destruct() {
		fclose($this->resource);
	}

	public function getResourceId() {
		return (string)$this->resource;
	}

	public static function createConnectionSignature($host, $username, $password, $client_flags) {
		return md5("$host::$username::$password::$client_flags");
	}

	public function getSignature() {
		return $this->signature;
	}
	
	/**
	 * mysql_select_db
	 * http://www.php.net/manual/en/function.mysql-select-db.php
	 */
	public function mysql_select_db($databaseName) {

		// Select the DB
		try {
			$this->params['databaseName'] = $databaseName;
			return $this->mysql_query("USE {$databaseName}");
		} catch (PDOException $e) {
			return false;
		}

		return false;
	}
	
	/**
	 * mysql_query
	 * http://www.php.net/manual/en/function.mysql-query.php
	 */
	public function mysql_query($query) {

		try {
			if ($res = $this->pdo->query($query)) {
				$this->params['rowCount'] = $res->rowCount();
				$this->params['lastQuery'] = $res;
				$this->loadError(false);
				return $res;
			}
		} catch (PDOException $e) {
			$this->loadError($e);
		}

		$this->params['rowCount'] = -1;
		$this->params['lastQuery'] = false;

		// Set query error
		$error_code = $this->pdo->errorCode();
		$error_info = $this->pdo->errorInfo();
		$this->params['errno'] = $error_code[0];
		$this->params['error'] = $error_info[2];
		return false;
	}
	
	/**
	 * mysql_unbuffered_query
	 * http://www.php.net/manual/en/function.mysql-unbuffered-query.php
	 */
	public function mysql_unbuffered_query($query) {
		return $this->mysql_query($query);
	}

	/**
	 * mysql_fetch_array
	 * http://www.php.net/manual/en/function.mysql-fetch-array.php
	 */
	public function mysql_fetch_array(&$result, $resultType = 3, $doCounts = false, $elementId = false) {
		static $last = null;

		if ($result === false) {
			echo 'Warning: mysql_fetch_*(): supplied argument is not a valid MySQL result resource' . PHP_EOL;
			return false;
		}

		if ($doCounts === true) {
			return $this->mysqlGetLengths($last, $elementId);
		}

		$hash = false;

		// Set retrieval type
		if (!is_array($result)) {
			$hash = spl_object_hash($result);
			switch ($resultType) {
				case 1:
					// by field names only as array
					$result = $result->fetchAll(PDO::FETCH_ASSOC);
					break;
				case 2:
					// by field position only as array
					$result = $result->fetchAll(PDO::FETCH_NUM);
					break;
				case 3:
					// by both field name/position as array
					$result = $result->fetchAll();
					break;
				case 4:
					// by field names as object
					$result = $result->fetchAll(PDO::FETCH_OBJ);
					break;
			}
		}
		
		// Row seek
		if ($hash !== false && isset($this->row_seek[$hash])) {
			// Check valid skip
			$rowNumber = $this->row_seek[$hash];
			if ($rowNumber > count($result) - 1) {
				echo "Warning: mysql_data_seek(): Offset $rowNumber is invalid for MySQL result (or the query data is unbuffered)" . PHP_EOL;
			}

			while($rowNumber > 0) {
				next($result);
				$rowNumber--;
			}
			
			unset($this->row_seek[$hash]);
		}

		$last = current($result);
		next($result);

		return $last;
	}
	
	/**
	 * mysql_fetch_assoc
	 * http://www.php.net/manual/en/function.mysql-fetch-assoc.php
	 */
	public function mysql_fetch_assoc(&$result) {
		return $this->mysql_fetch_array($result, 1);
	}
	
	/**
	 * mysql_fetch_row
	 * http://www.php.net/manual/en/function.mysql-fetch-row.php
	 */
	public function mysql_fetch_row(&$result) {
		return $this->mysql_fetch_array($result, 2);
	}
	
	/**
	 * mysql_fetch_object
	 * http://www.php.net/manual/en/function.mysql-fetch-object.php
	 */
	public function mysql_fetch_object(&$result) {
		return $this->mysql_fetch_array($result, 4);
	}
	
	/**
	 * mysql_num_fields
	 * http://www.php.net/manual/en/function.mysql-num-fields.php
	 */
	public function mysql_num_fields($result) {
		if (is_array($result)) {
			return count($result);
		}

		$data = $result->fetch(PDO::FETCH_NUM);
		return count($data);
	}
	
	/**
	 * mysql_num_rows
	 * http://www.php.net/manual/en/function.mysql-num-rows.php
	 */
	public function mysql_num_rows($result) {
		if (is_array($result)) {
			return count($result);
		}
		
		// Hard clone (cloning PDOStatements doesn't work)
		$query = $result->queryString;
		$cloned = $this->mysql_query($query);
		$data = $cloned->fetchAll();
		return count($data);
	}

	/**
	 * mysql_ping
	 * http://www.php.net/manual/en/function.mysql-ping.php
	 */
	public function mysql_ping() {

		try {
			$this->pdo->query('SELECT 1');
			$this->loadError(false);
		} catch (PDOException $e) {
			try {
				// Reconnect
				$set = $this->mysql_connect(
					$this->params['server'],
					$this->params['username'],
					$this->params['password'],
					$this->params['newLink'],
					$this->params['clientFlags'],
				);
			} catch (PDOException $e) {
				$this->loadError($e);
				return false;
			}

			// Select db if any
			if (isset($this->params['databaseName'])) {
				$set = $this->mysql_select_db($this->params['databaseName']);
				
				if (!$set) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * mysql_affected_rows
	 * http://www.php.net/manual/en/function.mysql-affected-rows.php
	 */
	public function mysql_affected_rows() {
		
		return $this->params['rowCount'];
	}

	/**
	 * mysql_client_encoding
	 * http://www.php.net/manual/en/function.mysql-client-encoding.php
	 */
	public function mysql_client_encoding() {

		$res = $this->pdo->query('SELECT @@character_set_database')->fetch(PDO::FETCH_NUM);

		return $res[0];
	}
	
	/**
	 * mysql_close
	 * http://www.php.net/manual/en/function.mysql-close.php
	 */
	public function mysql_close() {

		if (isset($this->pdo)) {
			$this->pdo = null;
			unset($this->pdo);
			return true;
		}
		
		return false;
	}
	
	/**
	 * mysql_create_db
	 * http://www.php.net/manual/en/function.mysql-create-db.php
	 */
	public function mysql_create_db($databaseName) {
		return $this->pdo->prepare('CREATE DATABASE ' . $databaseName)->execute();
	}

	/**
	 * mysql_data_seek
	 * http://www.php.net/manual/en/function.mysql-data-seek.php
	 */
	public function mysql_data_seek($result, $rowNumber) {
		// Set seek
		$this->row_seek[spl_object_hash($result)] = $rowNumber;
		return true;
	}

	/**
	 * mysql_list_dbs
	 * http://www.php.net/manual/en/function.mysql-list-dbs.php
	 */
	public function mysql_list_dbs() {

		return $this->pdo->query('SHOW DATABASES');
	}
	
	/**
	 * mysql_db_name
	 * http://www.php.net/manual/en/function.mysql-db-name.php
	 */
	public function mysql_db_name(&$result, $row, $field = 'Database') {
		// Get list if not gotten yet (still PDOStatement)
		if (!is_array($result)) {
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
		}

		if (isset($result[$row][$field])) {
			return $result[$row][$field];
		}

		return '';
	}
	
	/**
	 * mysql_db_query
	 * http://www.php.net/manual/en/function.mysql-db-query.php
	 */
	public function mysql_db_query($databaseName, $query) {
		
		$this->mysql_select_db($databaseName);
		
		return $this->mysql_query($query);
	}
	
	/**
	 * mysql_drop_db
	 * http://www.php.net/manual/en/function.mysql-drop-db.php
	 */
	public function mysql_drop_db($databaseName) {

		return $this->pdo->prepare('DROP DATABASE ' . $databaseName)->execute();
	}
	
	/**
	 * mysql_thread_id
	 * http://www.php.net/manual/en/function.mysql-thread-id.php
	 */
	public function mysql_thread_id() {

		$res = $this->pdo
			->query('SELECT CONNECTION_ID()')->fetch(PDO::FETCH_NUM);
			
		return $res[0];
	}
	
	/**
	 * mysql_list_tables
	 * http://www.php.net/manual/en/function.mysql-list-tables.php
	 */
	public function mysql_list_tables($databaseName) {

		return $this->pdo->query('SHOW TABLES FROM ' . $databaseName);
	}
	
	/**
	 * mysql_tablename
	 * http://www.php.net/manual/en/function.mysql-tablename.php
	 */
	public function mysql_tablename(&$result, $row) {
		// Get list if not gotten yet (still PDOStatement)
		if (!is_array($result)) {
			$result = $result->fetchAll(PDO::FETCH_NUM);
		}

		$counter = count($result);
		for ($x = 0; $x < $counter; $x++) {
			if ($x == $row) {
				return $result[$row][0];
			}
		}
		
		return '';
	}
	
	/**
	 * mysql_fetch_lengths
	 * http://www.php.net/manual/en/function.mysql-fetch-lengths.php
	 */
	public function mysql_fetch_lengths(&$result) {
		// Get list if not gotten yet (still PDOStatement)
		return $this->mysql_fetch_array($result, false, true);
	}
	
	/**
	 * mysql_field_len
	 * http://www.php.net/manual/en/function.mysql-field-len.php
	 */
	public function mysql_field_len(&$result, $fieldOffset = false) {
		if (!is_array($result)) {
			$result = $result->fetchAll(PDO::FETCH_NUM);
			$result = current($result);
		}

		return $this->mysqlGetLengths($result, $fieldOffset);
	}

	/**
	 * mysql_field_flags
	 * http://www.php.net/manual/en/function.mysql-field-flags.php
	 */
	public function mysql_field_flags(&$result, $fieldOffset = false) {
		return $this->getColumnMeta($result, 'flags', $fieldOffset);
	}
	
	/**
	 * mysql_field_name
	 * http://www.php.net/manual/en/function.mysql-field-name.php
	 */
	public function mysql_field_name(&$result, $fieldOffset = false) {
		return $this->getColumnMeta($result, 'name', $fieldOffset);
	}
	
	/**
	 * mysql_field_type
	 * http://www.php.net/manual/en/function.mysql-field-type.php
	 */
	public function mysql_field_type(&$result, $fieldOffset = false) {
		return $this->getColumnMeta($result, 'type', $fieldOffset);
	}
	
	/**
	 * mysql_field_table
	 * http://www.php.net/manual/en/function.mysql-field-table.php
	 */
	public function mysql_field_table(&$result, $fieldOffset = false) {
		return $this->getColumnMeta($result, 'table', $fieldOffset);
	}
	/**
	 * mysql_fetch_field
	 * http://www.php.net/manual/en/function.mysql-fetch-field.php
	 */
	public function mysql_fetch_field(&$result, $fieldOffset = false) {
		return $this->getColumnMeta($result, false, $fieldOffset);
	}
		
	/**
	 * mysql_field_seek
	 * http://www.php.net/manual/en/function.mysql-field-seek.php
	 */
	public function mysql_field_seek(&$result, $fieldOffset = false) {
		$this->next_offset = $fieldOffset;
	}

	/**
	 * mysql_stat
	 * http://www.php.net/manual/en/function.mysql-stat.php
	 */
	public function mysql_stat()  {
		return $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO);
	}
	
	/**
	 * mysql_get_server_info
	 * http://www.php.net/manual/en/function.mysql-get-server-info.php
	 */
	public function mysql_get_server_info()  {
		return $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
	
	/**
	 * mysql_get_proto_info
	 * http://www.php.net/manual/en/function.mysql-get-proto-info.php
	 */
	public function mysql_get_proto_info() {

		$res = $this->pdo
			->query("SHOW VARIABLES LIKE 'protocol_version'")->fetch(PDO::FETCH_NUM);
			
		return (int) $res[1];
	}
	
	/**
	 * mysql_get_host_info
	 * http://www.php.net/manual/en/function.mysql-get-server-info.php
	 */
	public function mysql_get_host_info()  {
		return $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}
	
	/**
	 * mysql_get_client_info
	 * http://www.php.net/manual/en/function.mysql-get-client-info.php
	 */
	public function mysql_get_client_info()  {
		return $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}
	
	/**
	 * mysql_free_result
	 * http://www.php.net/manual/en/function.mysql-free-result.php
	 */
	public function mysql_free_result(&$result)  {
		if (is_array($result)) {
			$result = false;
			return true;
		}

		if (get_class($result) != 'PDOStatement') {
			return false;
		}

		return $result->closeCursor();
	}
	
	/**
	 * mysql_result
	 * http://www.php.net/manual/en/function.mysql-result.php
	 */
	public function mysql_result(&$result, $row, $field = false) {

		// Get list if not gotten yet (still PDOStatement)
		if (!is_array($result)) {
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
		}

		$counter = count($result);
		for ($x = 0; $x < $counter; $x++) {
			if ($x == $row) {
				if ($field === false) {
					return current($result[$row]);
				} else {
					return $result[$row][$field];
				}
			}
		}
		
		return '';
	}
	
	/**
	 * mysql_list_processes
	 * http://www.php.net/manual/en/function.mysql-list-processes.php
	 */
	public function mysql_list_processes()  {
		return $this->pdo->query("SHOW PROCESSLIST");
	}

	/**
	 * mysql_set_charset
	 * http://www.php.net/manual/en/function.mysql-set-charset.php
	 */
	public function mysql_set_charset($charset)  {
		$set = "SET character_set_results = '$charset', character_set_client = '$charset', character_set_connection = '$charset', character_set_database = '$charset', character_set_server = '$charset'";
		return $this->pdo->query($set);
	}
	
	/**
	 * mysql_insert_id
	 * http://www.php.net/manual/en/function.mysql-insert-id.php
	 */
	public function mysql_insert_id() {
		return $this->pdo->lastInsertId();
	}
	
	/**
	 * mysql_list_fields
	 * http://www.php.net/manual/en/function.mysql-list-fields.php
	 */
	public function mysql_list_fields($databaseName, $tableName) {
		return $this->pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE  TABLE_SCHEMA = '$databaseName' AND TABLE_NAME = '$tableName'")->fetchAll();
	}
	
	/**
	 * mysql_errno
	 * http://www.php.net/manual/en/function.mysql-errno.php
	 */
	public function mysql_errno()  {
		return $this->params['errno'];
	}

	/**
	 * mysql_error
	 * http://www.php.net/manual/en/function.mysql-error.php
	 */
	public function mysql_error()  {
		return $this->params['error'];
	}
	
	/**
	 * mysql_real_escape_string
	 * http://www.php.net/manual/en/function.mysql-real-escape-string.php
	 */
	public function mysql_real_escape_string($string) {

		try {
			$string = $this->pdo->quote($string);
			$this->loadError(false);
			return substr($string, 1, -1);
		} catch (PDOException $e) {
			$this->loadError($e);
			return false;
		}
		
		return false;
	}
	
	/**
	 * mysql_escape_string
	 * http://www.php.net/manual/en/function.mysql-escape-string.php
	 */
	public function mysql_escape_string($string) {
		return $this->mysql_real_escape_string($string);
	}

	/**
	 * mysql_info
	 *
	 * Not sure how to get the actual result message from MySQL
	 * so the best I could do was to get the affected rows
	 * and construct a message that way. If you have a better way
	 * or know of a more accurate method, send it to me @
	 * azizsaleh@gmail.com and I'll update the code with it. All I got is
	 * the affected rows, so it will be missing changed, warnings,
	 * skipped, and rows matched
	 *
	 * http://www.php.net/manual/en/function.mysql-escape-string.php
	 */
	public function mysql_info() {

		$query = $this->params['lastQuery'];

		if (!isset($query->queryString)) {
			return false;
		}
		
		$affected = $this->params['rowCount'];

		if (strtoupper(substr($query->queryString, 0, 5)) == 'INSERT INTO') {
			return "Records: {$affected}  Duplicates: 0  Warnings: 0";
		}
		
		if (strtoupper(substr($query->queryString, 0, 9)) == 'LOAD DATA') {
			return "Records: {$affected}  Deleted: 0  Skipped: 0  Warnings: 0";
		}
		
		if (strtoupper(substr($query->queryString, 0, 11)) == 'ALTER TABLE') {
			return "Records: {$affected}  Duplicates: 0  Warnings: 0";
		}
		
		if (strtoupper(substr($query->queryString, 0, 6)) == 'UPDATE') {
			return "Rows matched: {$affected}  Changed: {$affected}  Warnings: 0";
		}
		
		if (strtoupper(substr($query->queryString, 0, 6)) == 'DELETE') {
			return "Records: 0  Deleted: {$affected}  Skipped: 0  Warnings: 0";
		}
		
		return false;
	}
	
	/**
	 * Close all connections
	 *
	 * @return void
	 */
	public function mysql_close_all() {
		// Free connections
		foreach ($this->_instances as $id => $pdo) {
			$this->_instances[$id] = null;
		}
		
		// Reset arrays
		$this->_instances = array(array());
		$this->params    = array();
	}

	/**
	 * get_resource_type function over ride
	 *
	 * @param RESOURCE $resource
	 *
	 * @return boolean
	 */
	public function get_resource_type($resource) {
		// mysql result resource type
		if (is_object($resource) && $resource instanceof PDOStatement) {
			return 'mysql result';
		}

		// Check if it is a mysql instance
		if (isset($this->_instances[$resource]) && !empty($this->_instances[$resource])) {
			// Check type
			if ($this->params[$resource]['clientFlags'] == PDO::ATTR_PERSISTENT){
				return 'mysql link persistent';
			} else {
				return 'mysql link';
			}
		}

		return get_resource_type($resource);
	}

	/**
	 * Get column meta information
	 *
	 * @param   object          $result
	 * @param   enum|boolean    $type   flags|name|type|table|len
	 * @param   int             $index
	 *
	 * @return  mixed
	 */
	protected function getColumnMeta(&$result, $type, $index) {
		// No index, but seek index
		if ($index === false && $this->next_offset !== false) {
			$index = $this->next_offset;
			// Reset
			$this->next_offset = false;
		}
		
		// No index, start @ 0 by default
		if ($index === false) {
			$index = 0;
		}
		
		if (is_array($result)) {
			return $result[$index][0];
		}

		$data = $result->getColumnMeta($index);

		switch ($type) {
			case 'flags':
				// Flags in PDO getColumMeta() is incomplete, so we will get flags manually
				return $this->getAllColumnData($data, true);
			case 'name':
				return $data['name'];
			case 'type':
				return $this->getPdoType($data['native_type']);
			case 'table':
				return $data['table'];
			// Getting all data (mysql_fetch_field)
			case false:
				// Calculate max_length of all field in the resultset
				$rows = $result->fetchAll(PDO::FETCH_NUM);
				$counter = count($rows);
				$maxLength = 0;
				for ($x = 0; $x < $counter; $x++) {
					$len = strlen($rows[$x][$index]);
					if ($len > $maxLength) {
						$maxLength = $len;
					}
				}
				return $this->getAllColumnData($data, false, $maxLength);
			default:
				return null;
		}
	}
	
	/**
	 * Get all field data, mimick mysql_fetch_field functionality
	 *
	 * @param   array   $data
	 * @param   boolean $simple
	 * @param   int     $maxLength
	 *
	 * @return  object
	 */
	protected function getAllColumnData($data, $simple = false, $maxLength = 0) {
		$type = $this->getPdoType($data['native_type']);

		// for zerofill/unsigned, we do a describe
		$query = $this->mysql_query("DESCRIBE `{$data['table']}` `{$data['name']}`");
		$typeInner = $this->mysql_fetch_assoc($query);

		// Flags
		if ($simple === true) {
			$string = in_array('not_null', $data['flags']) ? 'not_null' : 'null';
			$string .= in_array('primary_key', $data['flags']) ? ' primary_key' : '';
			$string .= in_array('unique_key', $data['flags']) ? ' unique_key' : '';
			$string .= in_array('multiple_key', $data['flags']) ? ' multiple_key' : '';

			$unSigned = strpos($typeInner['Type'], 'unsigned');
			if ($unSigned !== false) {
				$string .= ' unsigned';
			} else {
				$string .= strpos($typeInner['Type'], 'signed') !== false ? ' signed' : '';
			}

			$string .= strpos($typeInner['Type'], 'zerofill') !== false ? ' zerofill' : '';
			$string .= isset($typeInner['Extra']) ? ' ' . $typeInner['Extra'] : '';
			return $string;
		}

		$return = array (
			'name'          => $data['name'],
			'table'         => $data['table'],
			'def'           => $typeInner['Default'],
			'max_length'    => $maxLength,
			'not_null'      => in_array('not_null', $data['flags']) ? 1 : 0,
			'primary_key'   => in_array('primary_key', $data['flags']) ? 1 : 0,
			'multiple_key'  => in_array('multiple_key', $data['flags']) ? 1 : 0,
			'unique_key'    => in_array('unique_key', $data['flags']) ? 1 : 0,
			'numeric'       => ($type == 'int') ? 1: 0,
			'blob'          => ($type == 'blob') ? 1: 0,
			'type'          => $type,
			'unsigned'      => strpos($typeInner['Type'], 'unsigned') !== false ? 1 : 0,
			'zerofill'      => strpos($typeInner['Type'], 'zerofill') !== false ? 1 : 0,
		);
		
		return (object) $return;
	}
	
	/**
	 * Map PDO::TYPE_* to MySQL Type
	 *
	 * @param int   $type   PDO::TYPE_*
	 *
	 * @return string
	 */
	protected function getPdoType($type) {
		// Types enum defined @ http://lxr.php.net/xref/PHP_5_4/ext/mysqlnd/mysqlnd_enum_n_def.h#271
		$type = strtolower($type);
		switch ($type) {
			case 'tiny':
			case 'short':
			case 'long':
			case 'longlong';
			case 'int24':
				return 'int';
			case 'null':
				return null;
			case 'varchar':
			case 'var_string':
			case 'string':
				return 'string';
			case 'blob':
			case 'tiny_blob':
			case 'long_blob':
				return 'blob';
			default:
				return $type;
		}
	}

	/**
	 * For now we handle single flags, future feature
	 * is to handle multiple flags with pipe |
	 *
	 * @param  string
	 * @return array
	 */
	protected function flagsToDriverOptions($flags) {
		if ($flags == false || empty($flags)) {
			return array();
		}
		
		// Array it
		if (!is_array($flags)) {
			$flags = array($flags);
		}

		/*
		 * I am only adding flags that are mappable in PDO
		 * unfortunatly if you were using MySQL SSL, you will
		 * need to manually add that flag in using PDO constants
		 * located here: http://php.net/manual/en/ref.pdo-mysql.php
		 */
		$driver_options = array();
		foreach ($flags as $flag) {
			switch ($flag) {
				// CLIENT_FOUND_ROWS (found instead of affected rows)
				case 2:
					$params = array(PDO::MYSQL_ATTR_FOUND_ROWS => true);
					break;
				// CLIENT_COMPRESS (can use compression protocol)
				case 32:
					$params = array(PDO::MYSQL_ATTR_COMPRESS => true);
					break;
				// CLIENT_LOCAL_FILES (can use load data local)
				case 128:
					$params = array(PDO::MYSQL_ATTR_LOCAL_INFILE => true);
					break;
				// CLIENT_IGNORE_SPACE (ignore spaces before '(')
				case 256:
					$params = array(PDO::MYSQL_ATTR_IGNORE_SPACE => true);
					break;
				// Persistent connection
				case 12:
					$params = array(PDO::ATTR_PERSISTENT => true);
					break;
			}
			
			$driver_options[] = $params;
		}

		return $driver_options;
	}

	/**
	 * Load data into array
	 *
	 * @param PDO|PDOException|false    $object
	 *
	 * @return void
	 */
	protected function loadError($object) {
		// Reset errors
		if ($object === false || is_null($object)) {
			$this->params['errno'] = 0;
			$this->params['error'] = "";
			return;
		}
		// Set error
		$this->params['errno'] = $object->getCode();
		$this->params['error'] = $object->getMessage();
	}
	
	/**
	 * Get result set and turn them into lengths
	 *
	 * @param   array|object|null $resultSet
	 * @param   boolean           $elementId
	 *
	 * @return  array
	 */
	protected function mysqlGetLengths(&$resultSet = false, $elementId = false) {
		// If we don't have data
		if (empty($resultSet) || is_null($resultSet)) {
			if ($elementId === false) {
				return null;
			}
			return 0;
		}
		
		// Make sure it is an array
		if (!is_array($resultSet)) {
			$resultSet = (array) $resultSet;
		}
		
		// Return lengths
		$resultSet = array_map('strlen', $resultSet);
		
		if ($elementId === false) {
			return $resultSet;
		}

		return $resultSet[$elementId];
	}
 }