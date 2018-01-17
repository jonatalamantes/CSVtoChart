<?php 

	ini_set("auto_detect_line_endings", true);

    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph.php");
    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph_pie.php");
    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph_bar.php");
    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph_pie3d.php");
    require_once(__DIR__."/../Third-Party/PHPExcel/Classes/PHPExcel.php");
    require_once(__DIR__."/../Third-Party/PHPExcel/Classes/PHPExcel/IOFactory.php");

	class DataCSV
	{
		private $instances;
		private $atributes; 

		function __construct()
		{
			$this->instances = array();
			$this->atributes = array();
		}

		function getInstances()
		{
			return $this->instances;
		}

		function getAtributes()
		{
			return $this->atributes;
		}

		function setAtributes($attr = NULL)
		{
			$this->atributes = $attr;
		}

		function setInstances($inst = NULL)
		{
			$this->instances = $inst;
		}

        static function title($x = "")
        {
            $title = "";

            for ($i = 0; $i < strlen($x); $i++)
            {
                if ($x[$i] == strtoupper($x[$i]))
                {
                    $title .= " " . strtoupper($x[$i]);
                }
                else
                {
                    $title .= strtoupper($x[$i]);
                }
            }

            return $title;
        }

        function addInstance($inst)
        {
            $this->instances[] = $inst;
        }

		function getValue($instanceKey = 0, $dataKey = 0)
		{
			if (isset($this->instances[$instanceKey][$dataKey]))
			{
				return $this->instances[$instanceKey][$dataKey];
			}
			else
			{
				return "No Data in the CSV position";
			}
		}

		function getAtribute($key = 0)
		{
			if (isset($this->atributes[$key]))
			{
				return $this->atributes[$key];
			}
			else
			{
				return "";
			}
		}

        static function numeroToLetraExcel($num)
        {
            $x = $num-1;

            $letras = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
                            "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
                            "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", 
                            "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", 
                            "AY", "AZ");

            return $letras[$x];
        }

        function divideDataByAttr($attrR)
        {
            $keysArray = array();
            $dataCSVs  = array();

            foreach ($this->instances as $instkey => $inst) 
            {
                $pos = array_search($inst[$attrR], $keysArray);

                if ($pos === FALSE)
                {
                    $tempDataCSV = new DataCSV();
                    $tempDataCSV->setAtributes($this->getAtributes());

                    $tempDataCSV->addInstance($inst);
                    $dataCSVs[] = $tempDataCSV;

                    $keysArray[] = $inst[$attrR];
                }
                else
                {
                    $dataCSVs[$pos]->addInstance($inst);
                }
            }

            //var_dump($keysArray);

            $baseName = date("YmdHis");
            foreach ($dataCSVs as $key2 => $csvd) 
            {
                $name = $csvd->getInstances()[0][$attrR];
                $csvd->toFile(__DIR__."/../../CSVtoChart/App/Splits/$baseName-$name.csv");
            }

            return $dataCSVs;
        }

		function getFrecuencesTable($targets = NULL, $porcentaje = false)
		{
			$tableFrecuence = array();

			foreach ($this->atributes as $attrkey => $attr) 
			{
				$keysArray  = array();
				$countArray = array();
				$total      = 0;

				if ($targets !== NULL && !empty($targets))
				{
					if (array_search($attr, $targets) === FALSE)
					{
						continue;
					}
				}

				foreach ($this->instances as $instkey => $inst) 
				{
					$atomic = strtolower($this->getValue($instkey, $attr));
					$keyFound = array_search($atomic, $keysArray);

					if ($keyFound !== FALSE)
					{
						$countArray[$keyFound]++;
					}
					else
					{
						$countArray[] = 1;
						$keysArray[]  = $atomic;
					}

					$total++;
				}

				$statics = array();

                $id = 1;
				foreach ($keysArray as $key => $value) 
				{
					if ($porcentaje === "true")
					{
						$val = strval(number_format($countArray[$key]/$total*100, 3));

                        $vals = explode(",", $keysArray[$key]);
                        $nv   = array();

                        foreach ($vals as $key2 => $value2) 
                        {
                            if (trim($value2) != "")
                            {
                                //$nv[] = substr($value2, 0,3);
                                $nv[] = trim($value2);
                            }
                        }

                        $nv = implode(",", $nv);
                        //var_dump($nv);

						$statics[] = array("key" => $nv, "value" => $val, "id" => $id);
					}
					else
					{
                        $vals = explode(",", $keysArray[$key]);
                        $nv   = array();

                        foreach ($vals as $key2 => $value2) 
                        {
                            if (trim($value2) != "")
                            {
                                //$nv[] = substr($value2, 0,3);
                                $nv[] = trim($value2);
                            }
                        }

                        $nv = implode(" - ", $nv);

                        if (trim($nv) == "")
                        {
                            $nv = "sin especificar";
                        }

                        //var_dump($nv);

						$statics[] = array("key" => $nv, "value" => $countArray[$key], "id" => $id);
					}

                    $id++;
				}

				$nodeFrecuence = array();
				$nodeFrecuence["atribute"] = $attr;
				$nodeFrecuence["statics"]  = $statics;

				$tableFrecuence[] = $nodeFrecuence;

			}

			if (empty($tableFrecuence))
			{
				return NULL;
			}
			else
			{
				return $tableFrecuence;
			}
		}

		static function FrecuenceTableToHTML($tf = NULL)
		{
			if ($tf == NULL || empty($tf))
			{
				return "";
			}

			$id     = DataCSV::randomTime();
			$strRtr = "";

			$strRtr .= "<table class='ft table tablesorter table-bordered' id='$id'>";
			$strRtr .= "<thead>";
			$strRtr .= "<tr>";
			$strRtr .= "<td colspan=3 class='tf-header'>";
			$strRtr .= self::title(str_replace("_", " ", $tf["atribute"]));
			$strRtr .= "</td>";
			$strRtr .= "</tr>";
			$strRtr .= "<tr>";
            $strRtr .= "<th class='tf-attr-header'>No.</th>";
            $strRtr .= "<th class='tf-attr-header'>Valor</th>";
			$strRtr .= "<th class='tf-attr-header'>Cantidad</th>";
			$strRtr .= "</tr>";
			$strRtr .= "</thead>";
			$strRtr .= "<tbody>";

			$sum = 0;
			$cnt = 0;
			foreach ($tf["statics"] as $key => $static) 
			{
				$strRtr .= "<tr>";
				$sum += $static["value"];
				$strRtr .= "<td>";
                $strRtr .= $static["id"];
                $strRtr .= "</td>";
                $strRtr .= "<td>";
                $strRtr .= strtoupper(str_replace("_", " ", $static["key"]));
                $strRtr .= "</td>";
                $strRtr .= "<td>";
				$strRtr .= $static["value"];
				$strRtr .= "</td>";
				$strRtr .= "</tr>";

				$cnt++;
			}

			$strRtr .= "</tbody>";
			$strRtr .= "</table>";
			$strRtr .= "<p>Total: " . ceil($sum) . "<br>Distintos: " . ceil($cnt) . "<p>";

			$strRtr .= "<script>$('#$id').tablesorter()</script>"; 	

	    	return $strRtr;
		}

		static function FrecuenceTableToFileCSV($tf, $nameR = "", $maxInstances = 20)
		{
			if ($tf == NULL || empty($tf))
			{
				return "";
			}

			$name = $nameR;
			if ($name == "")
			{
				$name = DataCSV::randomTime();
			}

			if (($handle = fopen(__DIR__."/../../CSVtoChart/App/Frecuences/" . $name . ".csv", "w+")) !== FALSE) 
		    {
		    	fputcsv($handle, array("key", "value"));

		    	if (intval($maxInstances) == -1)
		    	{
			    	$tempArray = $tf["statics"];
			    	$temp2     = array();

			    	for ($i = 0; $i < sizeof($tf["statics"]); $i++)
			    	{
			    		$valueSearch = -1000;
			    		$posSearch   = -1;

			    		if ($i % 2 == 0)
			    		{	
			    			$valueSearch = 100000000000;
			    			$posSearch   = -1;

			    			//Search the minumum
			    			foreach ($tempArray as $keyArray => $valueArray) 
			    			{
			    				if ($valueArray !== NULL && floatval($valueArray["value"]) < floatval($valueSearch))
			    				{
			    					$posSearch   = $keyArray;
			    					$valueSearch = $valueArray["value"];
			    				}
			    			}
			    		}	
			    		else
			    		{
			    			$valueSearch = -100000000000;
			    			$posSearch   = -1;

			    			//Search the maximum
			    			foreach ($tempArray as $keyArray => $valueArray) 
			    			{
			    				if ($valueArray !== NULL && (floatval($valueArray["value"]) > floatval($valueSearch)))
			    				{
			    					$posSearch   = $keyArray;
			    					$valueSearch = $valueArray["value"];
			    				}
			    			}
			    		}	

			    		$temp2[] = $tempArray[$posSearch];
			    		unset($tempArray[$posSearch]);
			    	}

					foreach ($temp2 as $key => $static) 
					{
			            fputcsv($handle, $static);
			        }
		    	}
		    	else
		    	{
		    		$tempArray = $tf["statics"];
			    	$temp2     = array();

			    	for ($i = 0; $i < sizeof($tf["statics"]); $i++)
			    	{
			    		$valueSearch = -100000000000;
		    			$posSearch   = -1;

		    			//Search the maximum
		    			foreach ($tempArray as $keyArray => $valueArray) 
		    			{
		    				if ($valueArray !== NULL && (floatval($valueArray["value"]) > floatval($valueSearch)))
		    				{
		    					$posSearch   = $keyArray;
		    					$valueSearch = $valueArray["value"];
		    				}
		    			}

		    			$temp2[] = $tempArray[$posSearch];
			    		unset($tempArray[$posSearch]);
			    	}

			    	for ($i = 0; $i < $maxInstances; $i++) 
					{
                        if (array_key_exists($i, $temp2))
                        {
                            fputcsv($handle, $temp2[$i]);
                        }
			        }
		    	}

		        fclose($handle);
		        return $name;
		    }
		    else
		    {
		    	return "NF";
		    }
		}

		static function FrecuenceTableToD3Chart($tf, $sort = "", $name = "")
		{
			if ($tf == NULL || empty($tf))
			{
				return "";
			}

			if (sizeof($tf["statics"]) > 1000 || $tipo == "bar")
			{
				if ($sort !== "")
				{
					$tfTemp = $tf;
					DataCSV::sortBySubkey($tfTemp["statics"], $sort);					
					return DataCSV::FrecuencesTableToD3BarChart($tfTemp, $name);
				}

				return DataCSV::FrecuencesTableToD3BarChart($tf, $name);
			}
			else
			{
                if ($sort !== "")
                {
                    $tfTemp = $tf;
                    DataCSV::sortBySubkey($tfTemp["statics"], $sort);                   
                    return DataCSV::FrecuenceTableToD3PieChart($tf, $name);
                }

				return DataCSV::FrecuenceTableToD3PieChart($tf, $name);
			}
		}

		static function FrecuenceTableToD3PieChart($tf, $name)
		{
			if ($tf == NULL || empty($tf))
			{
				return "";
			}

			$nameFile = DataCSV::FrecuenceTableToFileCSV($tf, $name);

			if ($nameFile !== "NF")
			{
                $keys   = array();
                $keys2  = array();
                $values = array();

                $ct = 1;
                foreach ($tf["statics"] as $key => $value) 
                {
                    if ($ct == 20)
                    {
                        break;
                    }

                    $keys[]   = strtoupper($value["key"]) . " => " . $value["value"] . "\n";
                    $values[] = $value["value"];
                    $ct++;                    
                }

                // A new pie graph
                $graph = new PieGraph(1000,600);
                $graph->SetShadow();
                $graph->title->Set(self::title($tf["atribute"]));             
                              
                // Setup the pie plot
                $p1 = new PiePlot($values);
                 
                // Adjust size and position of plot
                $p1->SetSize(0.3);
                $p1->SetCenter(0.1,-0.52);
                
                // Enable and set policy for guide-lines
                $p1->SetGuideLines();
                //$p1->SetGuideLinesAdjust(0);

                // Setup slice labels and moe them into the plot
                $p1->value->SetFont(FF_FONT2);
                $p1->value->SetColor("black");
                $p1->SetLabelType(PIE_VALUE_ABS);
                $p1->SetLabels($keys, 1.0); 
                $p1->value->Show(); 
                 
                // Finally add the plot
                $graph->Add($p1);                 
                $gd = $graph->Stroke(_IMG_HANDLER);

                $file =  __DIR__."/../App/Charts/$nameFile.png";
                imagepng($gd, $file);
                imagedestroy($gd);

				$script .= "<img src='Charts/$nameFile.png'>";

				return $script;
			}
			else
			{
				return "";
			}
		}

		static function FrecuencesTableToD3BarChart($tf, $name)
		{
            if ($tf == NULL || empty($tf))
            {
                return "";
            }

            $nameFile = DataCSV::FrecuenceTableToFileCSV($tf, $name);

            if ($nameFile !== "NF")
            {
                $keys   = array();
                $values = array();

                $ct = 1;
                foreach ($tf["statics"] as $key => $value) 
                {
                    if ($ct == 20)
                    {
                        break;
                    }

                    $keys[]   = $value["id"];
                    $values[] = $value["value"];
                    $ct++;
                }

                // A new pie graph
                $graph = new Graph(1000,600, 'auto');
                $graph->SetShadow();
				$graph->SetScale('textlin');
				$graph->graph_theme = null;
				$graph->SetFrame(false);
                $graph->xaxis->SetTickLabels($keys); 
                $graph->title->Set(self::title($tf["atribute"]));				

                // Setup the pie plot
                $p1 = new BarPlot($values);
                $p1->SetFillColor("green");
                $p1->value->Show();
                $p1->value->SetColor("black");
                $p1->value->SetFont(FF_FONT1, FS_BOLD);
                $p1->value->SetFormat( "%0.1f");

                //var_dump($values);

                // Finally add the plot
                $graph->Add($p1);                 
                $gd = $graph->Stroke(_IMG_HANDLER);

                $file =  __DIR__."/../App/Charts/$nameFile.png";
                imagepng($gd, $file);
                imagedestroy($gd);

                $script .= "<img src='Charts/$nameFile.png'>";

                return $script;
            }
            else
            {
                return "";
            }
        }

       /** 
        * Sort one associative array by one subkey
        *
        * @author Jonathan Sandoval    <jonathan_s_pisis@hotmail.com>
        * @param  &array        array reference
        * @param  string        subkey name
        * @param  typeSort      constant of the type of sort
        */
        static function sortBySubkey(&$array = null, $subkey = "", $sortType = SORT_DESC) 
        {
            if ($array !== NULL)
            {
                foreach ($array as $subarray) 
                {
                    $keys[] = $subarray[$subkey];
                }

                array_multisort($keys, $sortType, $array);
            }
        }

		static function randomTime()
		{
			$tt = DateTime::createFromFormat('U.u', microtime(true));
			$tt = $tt->format("YmdHisu");

			return $tt;
		}

		/**
		 * @param  [type]
		 * @return [int] -1 on Sucess
		 */
		function fromFile($fileURL)
		{			
			if (($handle = @fopen($fileURL, "r")) !== FALSE) 
			{
				$row = 0;
				$atributesArray = array();
				$instancesArray = array();

			    while (($data = fgetcsv($handle, 0)) !== FALSE) 
			    {
			    	if ($row == 0)
			    	{
						for ($c = 0; $c < count($data); $c++) 
				        {
				            $atributesArray[] = $data[$c];
				        }
			    	}
			    	else
			    	{
			    		$instanceArrayInt = array();

			    		for ($c = 0; $c < sizeof($atributesArray); $c++) 
				        {
				            $instanceArrayInt[$atributesArray[$c]] = $data[$c];
				        }

			    		$instancesArray[] = $instanceArrayInt;
			    	}

			        $row++;
			    }

			    fclose($handle);

			    if (empty($atributesArray) || empty($instancesArray))
			    {
			    	return 2;
			    }
			    else
			    {
			    	$this->atributes = $atributesArray;
			    	$this->instances = $instancesArray;

			    	return -1;
			    }
			}

			return 1;
		}

		function toFile($fileURL)
		{
            $str = "";

			foreach ($this->getAtributes() as $attr)
            {
                    $str .= "\"" . $attr . "\",";       
            }

            $str = substr($str, 0, sizeof($str)-2);
            $str .= "\r\n";

            foreach ($this->getInstances() as $key => $inst) 
            {
                foreach ($inst as $key => $node) 
                {
                    $str .= "\"" . $node . "\",";       
                }

                $str = substr($str, 0, sizeof($str)-2);
                $str .= "\r\n";
            }

            $f = fopen($fileURL, "w+");
            fwrite($f, $str);
            fclose($f);
		}

        static function prepareExcel($name = "Reporte")
        {
            $objPHPExcel = new PHPExcel();

            $objPHPExcel->getProperties()->setCreator("Jonathan Elias Sandoval Talamantes")
                         ->setLastModifiedBy("Jonathan Elias Sandoval Talamantes")
                         ->setTitle("Office 2007 XLSX $name")
                         ->setSubject("Office 2007 XLSX $name")
                         ->setDescription("$name")
                         ->setKeywords("office 2007 openxml php")
                         ->setCategory("Report File");

            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle("Worksheet");
            $objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(true);

            return $objPHPExcel;
        }

        static function createSheetExcel($objPHPExcel = NULL, $name = "name")
        {
            if ($objPHPExcel != NULL)
            {
                $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, $name);
                $objPHPExcel->addSheet($myWorkSheet, 0);

                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(true);   
            }
        }

        static function FrecuenceTableToExcel($objPHPExcel = null, $tf = null, $nameR = "", $maxInstances = -1)
        {
            if ($objPHPExcel != NULL && $tf != NULL)
            {
                DataCSV::sortBySubkey($tf["statics"], "value", SORT_DESC);

                $objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
                $objPHPExcel->getActiveSheet()->setCellValue('A1', self::title(str_replace("_", " ", $tf["atribute"])));

                $objPHPExcel->getActiveSheet()->setCellValue('A2', "Valor");
                $objPHPExcel->getActiveSheet()->setCellValue('B2', "Cantidad");

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

                $objPHPExcel->getActiveSheet()->getStyle('A1:B2')->applyFromArray
                (
                    array(
                        'font'    => array(
                            'bold'      => true,
                            'color'     => array(
                                'argb' => 'FFFFFFFF'
                            ),
                            'size'     => 12
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => '00000000'),
                            )
                        ),
                        'fill' => array(
                            'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor'     => array(
                                'argb' => '00000000'
                            )
                        )
                    )
                );

                $sum = 0;
                $cnt = 3;
                foreach ($tf["statics"] as $key => $static) 
                {
                    $objPHPExcel->getActiveSheet()->setCellValue("A".$cnt, strtoupper(str_replace("_", " ", $static["key"])));
                    $objPHPExcel->getActiveSheet()->setCellValue("B".$cnt, $static["value"]);

                    $sum += $static["value"];
                    $cnt++;
                }

                $objPHPExcel->getActiveSheet()->getStyle('A3:B'.strval($cnt-1))->applyFromArray
                (
                    array(
                        'font'    => array(
                            'bold'      => false,
                            'size'     => 10
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => '00000000'),
                            )
                        ),
                    )
                );

                $cnt+=2;

                $objPHPExcel->getActiveSheet()->setCellValue("A".$cnt, "TOTAL");
                $objPHPExcel->getActiveSheet()->setCellValue("B".$cnt, ceil($sum));

                $objPHPExcel->getActiveSheet()->setCellValue("A".($cnt+1), "DISTINTOS");
                $objPHPExcel->getActiveSheet()->setCellValue("B".($cnt+1), ceil($cnt-5));

                $objPHPExcel->getActiveSheet()->getStyle('A'.$cnt.':B'.($cnt+1))->applyFromArray
                (
                    array(
                        'font'    => array(
                            'bold'     => true,
                            'size'     => 10,
                            'color'     => array(
                                'argb' => 'FFFFFFFF'
                            )
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => '00000000'),
                            )
                        ),
                        'fill' => array(
                            'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor'     => array(
                                'argb' => '82828282'
                            )
                        )
                    )
                );

            }
        }

        static function createGraphExcel($objPHPExcel = null, $tf = null)
        {
            if ($objPHPExcel != NULL && $tf != NULL)
            {
                $cnt = sizeof($tf["statics"]);

                if ($cnt >= 20)
                {
                    $cnt = 20;
                }

                $dataSeriesLabels1 = array(
                    new PHPExcel_Chart_DataSeriesValues('String', $tf["atribute"].'!$A$1', NULL, 1)
                );

                $xAxisTickValues1 = array(
                    new PHPExcel_Chart_DataSeriesValues('String', $tf["atribute"].'!$A$3:$A$'.strval($cnt+2), NULL, 4)
                );

                $dataSeriesValues1 = array(
                    new PHPExcel_Chart_DataSeriesValues('Number', $tf["atribute"].'!$B$3:$B$'.strval($cnt+2), NULL, 4),
                );

                $series1 = new PHPExcel_Chart_DataSeries(
                    PHPExcel_Chart_DataSeries::TYPE_PIECHART,              
                    NULL,                                                  
                    range(0, count($dataSeriesValues1)-1),                 
                    $dataSeriesLabels1,                                    
                    $xAxisTickValues1,                                     
                    $dataSeriesValues1                                     
                );

                $layout1 = new PHPExcel_Chart_Layout();
                $layout1->setShowVal(TRUE);
                $layout1->setShowPercent(TRUE);

                $plotArea1 = new PHPExcel_Chart_PlotArea($layout1, array($series1));
                $legend1 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);

                $title1 = new PHPExcel_Chart_Title($tf["atribute"]);

                //  Create the chart
                $chart1 = new PHPExcel_Chart(
                    "Titulo",
                    $title1,        // title
                    $legend1,       // legend
                    $plotArea1,     // plotArea
                    true,           // plotVisibleOnly
                    0,              // displayBlanksAs
                    NULL,           // xAxisLabel
                    NULL            // yAxisLabel       - Pie charts don't have a Y-Axis
                );

                //  Set the position where the chart should appear in the worksheet
                $chart1->setTopLeftPosition('D2');
                $chart1->setBottomRightPosition('P38');

                //  Add the chart to the worksheet
                $objPHPExcel->getActiveSheet()->addChart($chart1);
            }
        }

        static function outputExcel($objPHPExcel = null, $name = "Reporte")
        {
            if ($objPHPExcel != NULL)
            {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header("Content-Disposition: attachment;filename=\"$name\"");
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');

                // If you're serving to IE over SSL, then the following may be needed
                header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header ('Pragma: public'); // HTTP/1.0

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->setIncludeCharts(TRUE);
                $objWriter->save('php://output');
            }
        }

        /*static function FrecuenceTableToFileCSV($tf, $nameR = "", $maxInstances = 20)
        {
            if ($tf == NULL || empty($tf))
            {
                return "";
            }

            $name = $nameR;
            if ($name == "")
            {
                $name = DataCSV::randomTime();
            }

            if (($handle = fopen(__DIR__."/../../CSVtoChart/App/Frecuences/" . $name . ".csv", "w+")) !== FALSE) 
            {
                fputcsv($handle, array("key", "value"));

                if (intval($maxInstances) == -1)
                {
                    $tempArray = $tf["statics"];
                    $temp2     = array();

                    for ($i = 0; $i < sizeof($tf["statics"]); $i++)
                    {
                        $valueSearch = -1000;
                        $posSearch   = -1;

                        if ($i % 2 == 0)
                        {   
                            $valueSearch = 100000000000;
                            $posSearch   = -1;

                            //Search the minumum
                            foreach ($tempArray as $keyArray => $valueArray) 
                            {
                                if ($valueArray !== NULL && floatval($valueArray["value"]) < floatval($valueSearch))
                                {
                                    $posSearch   = $keyArray;
                                    $valueSearch = $valueArray["value"];
                                }
                            }
                        }   
                        else
                        {
                            $valueSearch = -100000000000;
                            $posSearch   = -1;

                            //Search the maximum
                            foreach ($tempArray as $keyArray => $valueArray) 
                            {
                                if ($valueArray !== NULL && (floatval($valueArray["value"]) > floatval($valueSearch)))
                                {
                                    $posSearch   = $keyArray;
                                    $valueSearch = $valueArray["value"];
                                }
                            }
                        }   

                        $temp2[] = $tempArray[$posSearch];
                        unset($tempArray[$posSearch]);
                    }

                    foreach ($temp2 as $key => $static) 
                    {
                        fputcsv($handle, $static);
                    }
                }
                else
                {
                    $tempArray = $tf["statics"];
                    $temp2     = array();

                    for ($i = 0; $i < sizeof($tf["statics"]); $i++)
                    {
                        $valueSearch = -100000000000;
                        $posSearch   = -1;

                        //Search the maximum
                        foreach ($tempArray as $keyArray => $valueArray) 
                        {
                            if ($valueArray !== NULL && (floatval($valueArray["value"]) > floatval($valueSearch)))
                            {
                                $posSearch   = $keyArray;
                                $valueSearch = $valueArray["value"];
                            }
                        }

                        $temp2[] = $tempArray[$posSearch];
                        unset($tempArray[$posSearch]);
                    }

                    for ($i = 0; $i < $maxInstances; $i++) 
                    {
                        if (array_key_exists($i, $temp2))
                        {
                            fputcsv($handle, $temp2[$i]);
                        }
                    }
                }

                fclose($handle);
                return $name;
            }
            else
            {
                return "NF";
            }
        }*/

	}

 ?>