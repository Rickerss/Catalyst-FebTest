<?php
	
	//Code is by Rick Epworth(epworth.rick@gmail.com). Feb 10 2016

	for($i = 1; $i <= 100; ++$i){
		echo $i;
		if($i % 3 == 0 && $i % 5 == 0){ //If the remainder of dividing the number by 3 and 5 are both 0, then it is a triple fiver.
			echo " --> triplefiver\n";
		}else if($i % 5 == 0){ //If the remainder of dividing the number by 5 is 0, then it's a fiver.
			echo " --> fiver\n";
		}else if($i % 3 ==0){ //If the remainder of dividing the number by 3 is 0, then it's a triple.
			echo " --> triple\n";
		}else{
			echo "\n";
		}		
	}
	
?>