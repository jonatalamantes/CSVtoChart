<?php 

	require_once(__DIR__."/../Backend/DataCSV.php");

	if (array_key_exists("nombreArchivo", $_GET))
	{
		$miDir  = __DIR__;
	    $miDir  = substr($miDir, 0, strpos($miDir, "App"));
	    $miFile = $miDir . "App/Uploads/" . $_GET["nombreArchivo"] . ".csv";

	    $csv = new DataCSV();

	    if ($csv->fromFile($miFile) < 0)
	    {
	    	$tg = explode("|", $_GET["targets"]);
	    	$tablesFrecuence = $csv->getFrecuencesTable($tg, $_GET["porcentaje"]);

	    	$excel = DataCSV::prepareExcel($_GET["nombreArchivo"]);

	    	foreach ($tablesFrecuence as $key => $tf) 
	    	{
	    		DataCSV::createSheetExcel($excel, $tf["atribute"]);
	    		DataCSV::FrecuenceTableToExcel($excel, $tf);
	    		DataCSV::createGraphExcel($excel, $tf);
	    	}

	    	DataCSV::outputExcel($excel, $_GET["nombreArchivo"]);
	    }
	}

 ?>