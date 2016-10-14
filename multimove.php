<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// config variables for your new settings
$args = array(
	'new_db_location' => 'localhost',
	'new_db_username' => 'root',
	'new_db_password' => '',
	'new_db_database' => 'wordpress_move',
	'new_db_prefix' => 'wp_',
	'new_wp_debug' => 'true',
	'new_site_host' => 'localhost',
	'new_site_domain_suffix' => 'mywdka',
	'modify_config_files' => true,
	'modify_database' => true,
	'new_url' => 'http://localhost/mywdka/'
);

new multimove($args);

class multimove{
	//global variables
	private $log = array();
	private $args = array();
	private $conn;

	//construct, make db connection
	function __construct($args){
		//set params
		$this->args = $args;

		//fire required scripts
		$this->args['modify_config_files'] ? $this->modify_config_files() : array_push($this->log, "Writing to config file was skipped due to wishes");
		$this->args['modify_database'] ? $this->modify_database_tables() : array_push($this->log, "Changing the database was skipped due to wishes");
	}

	//fn: modify the database tables
	function modify_database_tables(){
		//connect
		$this->connect();

		//update wp-options
		$this->update_wp_options();
	}

	//update wp-options
	function update_wp_options(){
		//update site_url
		$this->conn->query("
			UPDATE `wp_options`
			SET
				`wp_options`.`option_value` = '".$this->args['new_url']."'
			WHERE
				`wp_options`.`option_name` = 'siteurl'
		");
		array_push($this->log, "site_url updated");

		//update home
		$this->conn->query("
			UPDATE `wp_options`
			SET
				`wp_options`.`option_value` = '".$this->args['new_url']."'
			WHERE
				`wp_options`.`option_name` = 'home'
		");
		array_push($this->log, "home updated");
	}

	//mysql connection
	function connect(){
		$this->conn = mysqli_connect(
			$this->args['new_db_location'], 
			$this->args['new_db_username'], 
			$this->args['new_db_password'], 
			$this->args['new_db_database']
		);

		if($this->conn){
			array_push($this->log, "Database connected");
		}
	}

	//fn: modify wp_config.php
	function modify_config_files(){
		//check file rights
		if(!is_writable('wp-config.php')){
			array_push($this->log, "There was an error in file rights, please check if wp-config is writable");
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
				array_push($this->log, "DB_NAME modified");
				$found = true;
			}

			//change db user
			if(strpos(htmlentities($line), 'DB_USER')){
				$newContents .= htmlentities("define('DB_USER','".$this->args['new_db_username']."')\n");
				array_push($this->log, "DB_USER modified");
				$found = true;
			}

			//change db password
			if(strpos(htmlentities($line), 'DB_PASSWORD')){
				$newContents .= htmlentities("define('DB_PASSWORD','".$this->args['new_db_password']."')\n");
				array_push($this->log, "DB_PASSWORD modified");
				$found = true;
			}

			//change db host
			if(strpos(htmlentities($line), 'DB_HOST')){
				$newContents .= htmlentities("define('DB_HOST','".$this->args['new_site_host']."')\n");
				array_push($this->log, "DB_HOST modified");
				$found = true;
			}

			//change db prefix
			if(strpos(htmlentities($line), 'table_prefix')){
				$newContents .= htmlentities('$table_prefix  = \'' . $this->args['new_db_prefix'] . '\';' . "\n");
				array_push($this->log, "table_prefix modified");
				$found = true;
			}

			//change dp_debug
			if(strpos(htmlentities($line), '\'WP_DEBUG\'')){
				$newContents .= htmlentities("define('WP_DEBUG',".$this->args['new_wp_debug']."); \n");
				array_push($this->log, "WP_DEBUG set");
				$found = true;
			}

			//change dp_debug
			if(strpos(htmlentities($line), 'DOMAIN_CURRENT_SITE')){
				$newContents .= htmlentities("define('DOMAIN_CURRENT_SITE', '".$this->args['new_site_host']."');\n");
				array_push($this->log, "DOMAIN_CURRENT_SITE modified");
				$found = true;
			}

			//change dp_debug
			if(strpos(htmlentities($line), 'PATH_CURRENT_SITE')){
				$newContents .= htmlentities("define('PATH_CURRENT_SITE', '/".$this->args['new_site_domain_suffix']."/');\n");
				array_push($this->log, "PATH_CURRENT_SITE modified");
				$found = true;
			}

			if(!$found){
				$newContents .= htmlentities($line);
			}
		}

		//make backup of the current file
		copy('wp-config.php','wp-config.php.bak');

		//safe final file
		$fp = fopen('wp-config.php', 'w');
		fwrite($fp, html_entity_decode($newContents));
		fclose($fp);
	}


	//destruct, to give output
	function __destruct(){
		echo "<pre>";
		print_r($this->log);
		echo "</pre>";
	}
}
?>
