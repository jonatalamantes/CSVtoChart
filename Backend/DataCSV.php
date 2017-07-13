<?php 

	ini_set("auto_detect_line_endings", true);
	ini_set("upload_max_filesize", "7G");
	ini_set("memory_limit", "64G");
	ini_set("post_max_size", "8G");

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

				foreach ($keysArray as $key => $value) 
				{
					if ($porcentaje === "true")
					{
						$val = number_format($countArray[$key]/$total*100, 3);
						$statics[] = array("key" => $keysArray[$key], "value" => $val);
					}
					else
					{
						$statics[] = array("key" => $keysArray[$key], "value" => $countArray[$key]);
					}
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
			$strRtr .= "<td colspan=2 class='tf-header'>";
			$strRtr .= $tf["atribute"];
			$strRtr .= "</td>";
			$strRtr .= "</tr>";
			$strRtr .= "<tr>";
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
				$strRtr .= $static["key"];
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
			            fputcsv($handle, $temp2[$i]);
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

		static function FrecuenceTableToD3Chart($tf, $name = "", $tipo = "")
		{
			if ($tf == NULL || empty($tf))
			{
				return "";
			}

			if (sizeof($tf["statics"]) > 10 || $tipo == "bar")
			{
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
				$colorHash = array("#396AB1", "#DA7C30", "#3E9651", "#CC2529", 
								   "#535154", "#6B4C9A", "#922428", "#CCC210", 
								   "#808585", "#84BA64");

				$script  = "<svg width='960' height='500' id='ft-$nameFile'></svg>";
				$script .= "<script>";
				$script .= "
					var svg$nameFile = d3.select('#ft-$nameFile'),
					    width$nameFile = +svg$nameFile.attr('width'),
					    height$nameFile = +svg$nameFile.attr('height'),
					    radius$nameFile = Math.min(width$nameFile, height$nameFile) / 2,
					    g$nameFile = svg$nameFile.append('g')
					    		 		.attr('transform', 'translate(' + width$nameFile / 3.5 + ',' + height$nameFile / 2 + ')');\n";

				$script .= " var color$nameFile = d3.scaleOrdinal([";

				$counter = 0;
				foreach ($tf["statics"] as $key => $value) 
				{
					$script .= "'" . $colorHash[$counter] . "',";
					$counter++;
				}

				$script = substr($script, 0, strlen($script)-1);

				$script .= "]); ";

				$script .= "
					var pie$nameFile = d3.pie()
					    .sort(null)
					    .value(function(d) { return d.value; });

					var path$nameFile = d3.arc()
					    .outerRadius(radius$nameFile - 10)
					    .innerRadius(0);

					var label$nameFile = d3.arc()
					    .outerRadius(radius$nameFile - 40)
					    .innerRadius(radius$nameFile - 40);

					d3.csv('Frecuences/$nameFile.csv', function(d) {
					  d.value = +d.value;
					  return d;
					}, function(error, data) {
					  if (error) throw error;

					  var arc$nameFile = g$nameFile.selectAll('.arc')
					    .data(pie$nameFile(data))
					    .enter().append('g')
					      .attr('class', 'arc');

					  arc$nameFile.append('path')
					      .attr('d', path$nameFile)
					      .attr('dx', '0px')
					      .attr('fill', function(d) { return color$nameFile(d.data.value); });

					  arc$nameFile.append('rect')
					  	  .attr('x', '290px')
					      .attr('y', function(d) { return (d.index * 50 - 240) + 'px'})
					      .attr('width', '290px')
					      .attr('height', '30px')
					      .attr('stroke', function(d) { return color$nameFile(d.data.value); })
					      .attr('stroke-width', '5')
					      .attr('fill', 'white')

					  arc$nameFile.append('text')
					  	  .attr('dx', '300px')
					      .attr('dy', function(d) { return (d.index * 50 - 220) + 'px'})
					      .attr('font-size', '20px')
					      .attr('fill', 'black')
					      .text(function(d) { return d.data.key + ' => ' + d.data.value; });
					});
					</script>";

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

			$nameFile = DataCSV::FrecuenceTableToFileCSV($tf, $name, 20);

			if ($nameFile !== "NF")
			{
				$colorHash = array("#396AB1", "#DA7C30", "#3E9651", "#CC2529", 
								   "#535154", "#6B4C9A", "#922428", "#CCC210", 
								   "#808585", "#84BA64");

				$script  = "<svg width='960' height='500' id='ft-$nameFile'></svg>";
				$script .= "<script>";
				$script .= "
					var svg$nameFile = d3.select('#ft-$nameFile'),
					    margin$nameFile = {top: 20, right: 50, bottom: 30, left: 60},
					    width$nameFile = +svg$nameFile.attr('width') - margin$nameFile.left - margin$nameFile.right,
					    height$nameFile = +svg$nameFile.attr('height') - margin$nameFile.top - margin$nameFile.bottom;

					var x$nameFile = d3.scaleBand().rangeRound([0, width$nameFile]).padding(0.1),
					    y$nameFile = d3.scaleLinear().rangeRound([height$nameFile, 0]);

					var g$nameFile = svg$nameFile.append('g')
					    .attr('transform', 'translate(' + margin$nameFile.left + ',' + margin$nameFile.top + ')');

					d3.csv('Frecuences/$nameFile.csv', function(d) {
					  d.value = +d.value;
					  return d;
					}, function(error, data) {
					  if (error) throw error;

					  x$nameFile.domain(data.map(function(d) { return d.key; }));
					  y$nameFile.domain([0, d3.max(data, function(d) { return d.value; })]);

					  g$nameFile.append('g')
					      .attr('class', 'axis axis--y')
					      .call(d3.axisLeft(y$nameFile).ticks(10, '%'))
					    .append('text')
					      .attr('transform', 'rotate(-90)')
					      .attr('y', 6)
					      .attr('dy', '0.71em')
					      .attr('text-anchor', 'end')
					      .text('Value');

					  g$nameFile.selectAll('.bar')
					    .data(data)
					    .enter().append('rect')
					      .attr('class', 'bar')
					      .attr('x', function(d) { return x$nameFile(d.key); })
					      .attr('y', function(d) { return y$nameFile(d.value); })
					      .attr('width', x$nameFile.bandwidth())
					      .attr('fill', 'steelblue')
					      .attr('height', function(d) { return height$nameFile - y$nameFile(d.value); });

					  g$nameFile.append('g')
					      .attr('class', 'axis axis--x')
					      .attr('transform', 'translate(0,' + height$nameFile + ')')
					      .call(d3.axisBottom(x$nameFile));

					});
					</script>";

				return $script;
			}
			else
			{
				return "";
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