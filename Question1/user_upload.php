<?php
	
	function readFromFile($fname){
		$file = fopen($fname,"r");
		$firstLine = true;
		while(! feof($file)){
			if($firstLine){
				$firstLine = false;
				fgetcsv($file);
			}else{
				$person = fgetcsv($file);				
				$name = ucfirst(trim(strtolower($person[0])));
				$surname = ucfirst(trim(strtolower($person[1])));
				$email = trim(strtolower($person[2]));
				
				$emailFlag = filter_var($email, FILTER_VALIDATE_EMAIL);
				
				if($emailFlag){
					$sql = "INSERT INTO users (name, surname, email) VALUES(" . 
					$name . ", " . 
					$surname . ", " . 
					$email . ")";
					echo $sql, "\n";
				}else{
					fwrite(STDOUT, "ERROR: The supplied email address, \"" . $email . "\", is invalid\n");
				}
			}
		}
		fclose($file);
	}
	
	function dropTable($conn){
		$sql = "DROP TABLE IF EXISTS `users`";
		if ($conn->query($sql) === TRUE) {
			echo "Table users dropped successfully";
		} else {
			echo "Error dropping table: " . $conn->error;
		}
	}
	
	function createTable($conn){
		dropTable($conn);
		$sql = "CREATE TABLE users (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				firstname VARCHAR(30) NOT NULL,
				lastname VARCHAR(30) NOT NULL,
				email VARCHAR(50),
				reg_date TIMESTAMP
				)";

		if ($conn->query($sql) === TRUE) {
			echo "Table users created successfully";
		} else {
			echo "Error creating table: " . $conn->error;
		}
	}
	
	//Details for connection.
	$user = '';
	$pass = '';
	$host = '';
	$db = 'test';
	
	//Get connections details from arguments / arguments that do not need a connection.	
	//The other arguments all require the use of a connection, so we need to make sure we get these first.
	for($i = 1; $i < $argc; $i++){
		if($argv[$i] == "-p"){
			echo "Getting PASS", "\n";
			$pass = $argv[++$i];
		}elseif($argv[$i] == "-h"){
			echo "Getting HOST", "\n";
			$host = $argv[++$i];
		}elseif($argv[$i] == "--dry_run"){
			echo "DRY RUN", "\n";
		}elseif($argv[$i] == "-u"){
			echo "Getting USER", "\n";
			$user = $argv[++$i];
		}elseif($argv[$i] == "--help"){
			echo "SHOW HELP", "\n";
			echo "--file [csv file name] -> This is the name of the CSV file to be parsed.",
					"\n--create_table -> This will cause the MySQL users table to be built (no further action).",
					"\n--dry_run -> This will be used with the \"--file\" directive in an instance where we want to run the script but not insert into DB.",
					"\n-u [username] -> Sets the MySQL username to be used.",
					"\n-p [password] -> Sets the MySQL password to be used.",
					"\n-h [host] -> Sets the MySQL host to be used.";
		}
	}
	
	//Now that we have everything we need for a connection, let's try to connect.
	$conn = new mysqli($host, $user, $pass, $db);
	
	if($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
	}
	
	for($i = 1; $i < $argc; $i++){
		if($argv[$i] == "--file"){
			echo "Getting FNAME", "\n";
			$fname = $argv[++$i];
			readFromFile($fname);
		}elseif($argv[$i] == "--create_table"){
			echo "CREATE TABLE", "\n";
			createTable($conn);
		}//else{
			//echo "Unknown command line argument: ", $argv[$i], "\n";
		//}
	}
	
	$conn->close();

?>