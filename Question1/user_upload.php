<?php
	function readFromFile($conn, $fname, $dryrun){ //This is my function to read from the csv file.
		if($dryrun){
			fwrite(STDOUT, "\n[DRYRUN: Reading from file (\"" . $fname . "\")]");
		}else{
			fwrite(STDOUT, "\n[Reading from file (\"" . $fname . "\") and Inserting into DB]");
		}
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
				
				if($emailFlag){ //If it's a valid email...
					if(!$dryrun){
						insertRecord($conn, $name, $surname, $email);
					}else{
						fwrite(STDOUT, "\nPerson: " . $name . " " . $surname . " (" . $email . ")");
					}					
				}else{ //If email isn't valid, tell the user.
					fwrite(STDOUT, "\n[ERROR]: The supplied email address, \"" . $email . "\", is invalid");
				}
			}
		}
		fclose($file); //Close the file after we are done.
	}
	
	function insertRecord($conn, $name, $surname, $email){
		$sql = "INSERT INTO users (name, surname, email) VALUES(\"" . 
				$name . "\", \"" . 
				$surname . "\", \"" . 
				$email . "\");";
		
		//A little check to see if everything goes nicely during insertion.
		if ($conn->query($sql) === TRUE) {
			echo "\n" . $name . " " . $surname . "(" . $email . ") inserted successfully";
		} else {
			echo "\n[ERROR]: Inserting into table failed: " . $conn->error;
		}
	}
	
	function dropTable($conn){ //Simple function to drop the table 'users'.
		$sql = "DROP TABLE IF EXISTS users";
		if ($conn->query($sql) === TRUE) {
			echo "\nTable users dropped successfully";
		} else {
			echo "\n[ERROR]: Dropping table failed: " . $conn->error;
		}
	}
	
	function createTable($conn){ //Function to create a new 'users' table.
		fwrite(STDOUT, "\n[Creating Table users]");
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
			echo "\n[ERROR]: Creating table failed: " . $conn->error;
		}
	}
	
	//Details for connection.
	$conn = NULL;
	$user = '';
	$pass = '';
	$host = '';
	$db = 'test';
	
	//Other details
	$passGiven = false;
	$userGiven = false;
	$hostGiven = false;
	$dryrun = false;
	$cTable = false;
	$fileName = "";
	
	//Get all of our command line arguments. 
	//Not calling functions here because of obvious ordering issues that would occur.
	fwrite(STDOUT, "[Reading command line arguments]\n");
	for($i = 1; $i < $argc; $i++){
		if($argv[$i] == "-p"){
			echo "Password supplied", "\n";
			$pass = $argv[++$i];
			$passGiven = true;
		}elseif($argv[$i] == "-h"){
			echo "Host supplied", "\n";
			$host = $argv[++$i];
			$hostGiven = true;
		}elseif($argv[$i] == "-u"){
			echo "User supplied", "\n";
			$user = $argv[++$i];
			$userGiven = true;
		}elseif($argv[$i] == "--help"){
			echo "Help needed\n\n[Help]\n";
			echo "--file [csv file name] -> This is the name of the CSV file to be parsed.",
					"\n--create_table -> This will cause the MySQL users table to be built (no further action).",
					"\n--dry_run -> This will be used with the \"--file\" directive in an instance where we want to run the script but not insert into DB.",
					"\n-u [username] -> Sets the MySQL username to be used.",
					"\n-p [password] -> Sets the MySQL password to be used.",
					"\n-h [host] -> Sets the MySQL host to be used.";
		}elseif($argv[$i] == "--dry_run"){
			echo "Dryrun is TRUE", "\n";
			$dryrun = true;
		}elseif($argv[$i] == "--file"){
			echo "File name supplied", "\n";
			$fileName = $argv[++$i];
		}elseif($argv[$i] == "--create_table"){
			echo "Create table is TRUE", "\n";
			$cTable = true;
		}else{
			echo "[ERROR]: Unknown command line argument: ", $argv[$i], "\n";
		}
	}
	
	//Now that we have everything we need for a connection, let's try to connect.
	if(!$dryrun){ //We only make a connection to the DB when we are not on a dry run.
		fwrite(STDOUT, "\n[Connecting to DB]");
		if($passGiven && $userGiven && $hostGiven) { //If all details are given...
			$conn = new mysqli($host, $user, $pass, $db); //Try to make the connection.
		
			if($conn->connect_error) { //If it doesn't work, spit out the error and kill the program.
				die("\n[ERROR]: Connection failed: " . $conn->connect_error);
			}else{
				fwrite(STDOUT, "\nConnection successful");
			}
		}else{
			fwrite(STDOUT, "\n[ERROR]: To make a connection, the user(-u), the password(-p) and the host(-h) need to be supplied");
		}
	}
	
	//Now the appropriate functions will be called.
	if($cTable && $conn != "" && !$dryrun){ //If we have a connection, and we need to create a table...
		createTable($conn); //Create the table.
	}else if($cTable && $conn != "" && $dryrun){
		fwrite(STDOUT, "\n[ERROR]: Cannot alter the Database on a dry run (--dry_run)");
	}
	
	if($fileName != "" && !$cTable  && $conn != "" && !$dryrun){
		readFromFile($conn, $fileName, $dryrun);
	}
	
	if($dryrun && !$cTable  && $conn == ""){
		if($fileName != ""){
			readFromFile($conn, $fileName, $dryrun);
		}else{
			fwrite(STDOUT, "\n[ERROR]: Dry run(--dry_run) is TRUE but no file name(--file) given.");	
		}
	}
	
	if($conn != ""){
		$conn->close();
	}

?>