<?php 

	ini_set("auto_detect_line_endings", true);

    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph.php");
    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph_pie.php");
    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph_bar.php");
    require_once(__DIR__."/../Third-Party/jpgraph/src/jpgraph_pie3d.php");

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
						$val = strval(number_format($countArray[$key]/$total*100, 3)) . "%";
						$statics[] = array("key" => $keysArray[$key], "value" => $val, "id" => $id);
					}
					else
					{
						$statics[] = array("key" => $keysArray[$key], "value" => $countArray[$key], "id" => $id);
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
			$strRtr .= $tf["atribute"];
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
                $strRtr .= strtoupper($static["key"]);
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

		static function FrecuenceTableToFileCSV($tf, $nameR = "", $maxInstances = -1)
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

			if (sizeof($tf["statics"]) > 10 || $tipo == "bar")
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

                foreach ($tf["statics"] as $key => $value) 
                {
                    $keys[]   = strtoupper($value["key"]) . " => " . $value["value"] . "\n";
                    $values[] = $value["value"];
                }

                // A new pie graph
                $graph = new PieGraph(960,600);
                $graph->SetShadow();
                $graph->title->Set($tf["atribute"]);
                              
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
                    $keys[]   = $value["id"];
                    $values[] = $value["value"];
                    $ct++;
                }

                // A new pie graph
                $graph = new Graph(960,600, 'auto');
                $graph->SetShadow();
				$graph->SetScale('textlin');
				$graph->graph_theme = null;
				$graph->SetFrame(false);
                $graph->xaxis->SetTickLabels($keys); 
                $graph->title->Set($tf["atribute"]);				

                // Setup the pie plot
                $p1 = new BarPlot($values);
                $p1->SetFillColor("green");
                $p1->value->Show();
                $p1->value->SetColor("black");
                $p1->value->SetFont(FF_FONT1, FS_BOLD);
                $p1->value->SetFormat( "%0.1f");

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
        static function sortBySubkey(&$array = null, $subkey = "", $sortType = SORT_ASC) 
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
			//TODO
		}
	}

 ?>