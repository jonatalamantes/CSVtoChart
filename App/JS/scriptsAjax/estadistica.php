<?php 

	require_once(__DIR__."/../../../Backend/DataCSV.php");

	if (array_key_exists("nombreArchivo", $_POST))
	{
		$miDir  = __DIR__;
	    $miDir  = substr($miDir, 0, strpos($miDir, "App"));
	    $miFile = $miDir . "App/Uploads/" . $_POST["nombreArchivo"] . ".csv";

	    $csv = new DataCSV();

	    if ($csv->fromFile($miFile) < 0)
	    {
	    	//$csv->divideDataByAttr("municipio");

	    	$tg = explode("|", $_POST["targets"]);
	    	$tablesFrecuence = $csv->getFrecuencesTable($tg, $_POST["porcentaje"]);

	    	echo "<br>";
	    	foreach ($tablesFrecuence as $key => $tf) 
	    	{
	    		echo DataCSV::FrecuenceTableToD3Chart($tf, 'value');
	    		echo DataCSV::FrecuenceTableToHTML($tf);
	    		echo "<br>";
	    	}
	    }
	}

 ?>