<?php
session_start(); // ADDED: Needed for authentication

class database_data
{
	private $servername;
	private $username;
	private $password;
	private $dbname;

	static public $pdo;

	public function __construct($servername, $username, $password, $dbname)
	{
		$this->servername = $servername;
		$this->username = $username;
		$this->password = $password;
		$this->dbname = $dbname;
	}

	public function create_connection()
	{
		try {
			self::$pdo = new PDO("mysql:host=" . $this->servername . ";dbname=" . $this->dbname, $this->username, $this->password);
			self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // ADDED: Makes fetch() return cleaner arrays
		} catch(PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
		return self::$pdo;
	}
}

$db_connection = new database_data("localhost", "root", "", "reservation_system");
$db_connection->create_connection();
?>