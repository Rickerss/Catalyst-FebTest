<?php

	for($i = 1; $i <= 100; ++$i){
		echo $i;
		if($i % 3 == 0 && $i % 5 == 0){
			echo " --> triplefiver\n";
		}else if($i % 5 == 0){
			echo " --> fiver\n";
		}else if($i % 3 ==0){
			echo " --> triple\n";
		}else{
			echo "\n";
		}		
	}
	
?>