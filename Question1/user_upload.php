<?php
	//This is my fucntion to read from the csv file.
	function readFromFile($conn, $fname){
		$file = fopen($fname,"r"); //Open the file.
		$firstLine = true; //We don't need the first line (it's the headers).
		while(! feof($file)){ //While we are not at the end of the file...
			if($firstLine){ 
				$firstLine = false;
				fgetcsv($file); //Read in the first line and don't do anything with it.
			}else{
				$person = fgetcsv($file); //Read in all details.		
				$name = ucfirst(trim(strtolower($person[0]))); //Split details up..
				$surname = ucfirst(trim(strtolower($person[1]))); //ucfirst puts the first letter to uppercase.
				$email = trim(strtolower($person[2])); //trim trims whitespace, strtolower puts the string to lowercase.
				
				$emailFlag = filter_var($email, FILTER_VALIDATE_EMAIL); //Use php's function for checking valid emails.
				
				if($emailFlag){ //If it's a valid email then insert into table.
					$sql = "INSERT INTO users (name, surname, email) VALUES(\"" . 
					$name . "\", \"" . 
					$surname . "\", \"" . 
					$email . "\");";
					
					//A little check to see if everything went nicely.
					if ($conn->query($sql) === TRUE) {
						echo "\n" . $name . " " . $surname . "(" . $email . ") inserted successfully";
					} else {
						echo "\nERROR inserting into table: " . $conn->error;
					}
				}else{ //If email isn't valid, tell the user.
					fwrite(STDOUT, "\nERROR: The supplied email address, \"" . $email . "\", is invalid");
				}
			}
		}
		fclose($file); //Close the file after we are done.
	}
	
	function dropTable($conn){ //Simple function to drop the table 'users'.
		$sql = "DROP TABLE IF EXISTS users";
		if ($conn->query($sql) === TRUE) {
			echo "\nTable users dropped successfully";
		} else {
			echo "\nERROR dropping table: " . $conn->error;
		}
	}
	
	function createTable($conn){ //Function to create a new 'users' table.
		dropTable($conn); //If we already have one, then we have to get rid of it.
		$sql = "CREATE TABLE users (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				name VARCHAR(30) NOT NULL,
				surname VARCHAR(30) NOT NULL,
				email VARCHAR(50) NOT NULL,
				UNIQUE(email)
				)";

		if ($conn->query($sql) === TRUE) {
			echo "\nTable users created successfully";
		} else {
			echo "\nERROR creating table: " . $conn->error;
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
			readFromFile($conn, $fname);
		}elseif($argv[$i] == "--create_table"){
			echo "CREATE TABLE", "\n";
			createTable($conn);
		}//else{
			//echo "Unknown command line argument: ", $argv[$i], "\n";
		//}
	}
	
	$conn->close();

?>