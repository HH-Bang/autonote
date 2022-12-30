<?php
namespace lib;

class dbconn
{
	public $db_connection;

	public function Db_connect( $key='' )
	{
		if( empty($key) ){
			echo "DATABASE SELECT NOT KEY VALUE!";
			exit;
		}

		$ini_info = parse_ini_file( DBCONFIG_PATH, true );
		$hostname = $ini_info[$key]['hostname'];
		$username =  $ini_info[$key]['name'];
		$password = $ini_info[$key]['password'];
		$dbname = $ini_info[$key]['dbname'];
		$use_port = @$ini_info[$key]['port'];		

		$this->db_connection = mysqli_connect( $hostname, $username, $password, $dbname, $use_port ) or die ( "DATABASE NOT CONNECT!" );

		// 캐릭터셋 강제변경
		mysqli_query( $this->db_connection, "SET NAMES UTF8" );

		return $this->db_connection;
	}

	public function Db_close()
	{
		mysqli_close( $this->db_connection );
	}
}