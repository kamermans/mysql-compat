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

class ConnectionManager {

	/**
	 * @var ConnectionManager
	 */
	protected static $instance;

	/**
	 * @var Connection[]
	 */
	protected $connections = array();

	/**
	 * @return ConnectionManager
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function pushConnection(PDO $db) {
		// TODO: Implement
	}

	public function getConnection($link) {
		if ($link === false) {
			return $this->getDefaultConnection();
		}

		$resource_id = (string)$link;
		
		if (!array_key_exists($resource_id, $this->connections)) {
			throw new \InvalidArgumentException("Resource is not a valid MySqlCompat\Connection resource: $resource_id");
		}

		return $this->connections[$resource_id];
	}

	public function getDefaultConnection() {
		if (!$this->connections) {
			return $this->mysql_connect(null, null, null);
		}

		return $this->getLastConnection();
	}

	public function getLastConnection() {
		end($this->connections);
		return $this->getConnection(key($this->connections));
	}

	/**
	 * mysql_connect
	 * http://www.php.net/manual/en/function.mysql-connect.php
	 */
	public function mysql_connect($host, $username, $password, $newLink = false, $clientFlags = false) {
		$signature = Connection::createSignature($host, $username, $password, $clientFlags);

		if (!$newLink) {
			// Attempt to reuse an existing connection
			foreach ($this->connection as $connection) {
				if ($signature == $connection->getSignature()) {
					return $connection->getResource();
				}
			}
		}


		$flags = $this->translateFlags($clientFlags);
		
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
			$this->connections[$usePosition] = new PDO($dsn, $username, $password, $flags);

			return $usePosition;
		} catch (PDOException $e) {
			// Mock the instance for error reporting
			$this->connections[$usePosition] = array();
			$this->load_error($usePosition, $e);
			return false;
		}
		
		return false;
	}
	
	/**
	 * mysql_pconnect
	 * http://www.php.net/manual/en/function.mysql-pconnect.php
	 */
	public function mysql_pconnect($host, $username, $password, $newLink = false, $clientFlags = false) {
		$persistent = PDO::ATTR_PERSISTENT;
		$clientFlags = ($clientFlags !== false) ? array_merge($clientFlags, $persistent) : $persistent;
		return $this->mysql_connect($host, $username, $password, $newLink, $clientFlags);
	}
	
	/**
	 * Close all connections
	 *
	 * @return void
	 */
	public function mysql_close_all() {
		// Free connections
		foreach ($this->connections as $id => $pdo) {
			$this->connections[$id] = null;
		}
		
		// Reset arrays
		$this->connections = array();
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
		if (isset($this->connections[$resource]) && !empty($this->connections[$resource])) {
			// Check type
			if ($this->_params[$resource]['clientFlags'] == PDO::ATTR_PERSISTENT){
				return 'mysql link persistent';
			} else {
				return 'mysql link';
			}
		}

		return get_resource_type($resource);
	}
}