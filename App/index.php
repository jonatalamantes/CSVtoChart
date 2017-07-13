<?php 

	require_once(__DIR__."/../Backend/DataCSV.php");

	$string = file_get_contents("Templates/Main.html");
	$string = str_replace("|archivo|", DataCSV::randomTime(), $string);
	$string = str_replace("|title|", "CSV to Chart", $string);
	echo $string;

 ?>