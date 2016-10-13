<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// config variables for your new settings
$args = array(
	'new_db_location' => 'localhost',
	'new_db_username' => 'root',
	'new_db_password' => 'silvercouch6',
	'new_db_database' => 'wordpress_move',
	'new_site_domain' => 'localhost',
	'new_site_domain_suffix' => 'mywdka',
	'modify_config_files' => true,
	'mofify_database' => true
);

new multimove($args);

class multimove{
	//global variables
	private $error = array();
	private $args = array();

	//construct, make db connection
	function __construct($args){
		//set params
		$this->args = $args;

		//fire required scripts
		$this->args['modify_config_files'] ? $this->modify_config_files() : array_push($this->error, "Writing to config file was skipped due to wishes");

	}

	//fn: modify wp_config.php
	function modify_config_files(){
		//check file rights
		if(!is_writable('wp-config.php')){
			array_push($this->error, "There was an error in file rights, please check if wp-config is writable");
			return;
		}

		//open the file contents
		$newContents = "";
		$inputFile = fopen("wp-config.php", "r");
		while (($line = fgets($inputFile)) !== false) {
			$found = false;

			//change db name
			if(strpos(htmlentities($line), 'DB_NAME')){
				$newContents .= htmlentities("define('DB_NAME','".$this->args['new_db_database']."')\n");
				$found = true;
			}

			//change db user
			if(strpos(htmlentities($line), 'DB_USER')){
				$newContents .= htmlentities("define('DB_USER','".$this->args['new_db_username']."')\n");
				$found = true;
			}

			//change db password
			if(strpos(htmlentities($line), 'DB_PASSWORD')){
				$newContents .= htmlentities("define('DB_PASSWORD','".$this->args['new_db_password']."')\n");
				$found = true;
			}

			if(!$found){
				$newContents .= htmlentities($line);
			}

			//$newContents .= strpos(htmlentities($line), 'DB_USER') ? "define('DB_USER','".$this->args['new_db_username']."')\n" : htmlentities($line);
		}

		echo "<pre>";
		print_r($newContents);
		echo "</pre>";
	}


	//destruct, to give output
	function __destruct(){
		echo "<pre>";
		print_r($this->error);
		echo "</pre>";
	}
}
?>
