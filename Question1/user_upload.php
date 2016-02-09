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
					fwrite(STDOUT, "The supplied email address, " . $email . ", is invalid\n");
				}
			}
		}
		fclose($file);
	}
	
	for($i = 1; $i < $argc; $i++){
		if($argv[$i] == "--file"){
			echo "FILE STUFF", "\n";
			$fname = $argv[++$i];
			echo $fname, "\n";
			readFromFile($fname);
		}elseif($argv[$i] == "--create_table"){
			echo "CREATE TABLE", "\n";
		}elseif($argv[$i] == "--dry_run"){
			echo "DRY RUN", "\n";
		}elseif($argv[$i] == "--help"){
			echo "SHOW HELP", "\n";
			echo "--file [csv file name] -> This is the name of the CSV file to be parsed.",
					"\n--create_table -> This will cause the MySQL users table to be built (no further action).",
					"\n--dry_run -> This will be used with the \"--file\" directive in an instance where we want to run the script but not insert into DB.",
					"\n-u - MySQL username -> Sets the MySQL username to be used.",
					"\n-p - MySQL password -> Sets the MySQL password to be used.",
					"\n-h - MySQL host -> Sets the MySQL host to be used.\n";
		}else{
			echo "Unknown command line argument: ", $argv[$i], "\n";
		}
	}

?>