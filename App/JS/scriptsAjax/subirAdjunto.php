<?php

    require_once(__DIR__."/../../../Backend/DataCSV.php");

    $msj = "KO";

    $miDir = __DIR__;
    $miDir = substr($miDir, 0, strpos($miDir, "App"));

    if (!empty($_FILES) && array_key_exists("nombreArchivo", $_POST))
    {
        if ($_FILES["fichero_usuario"]["error"] == UPLOAD_ERR_OK)
        {
            $tmp_name = $_FILES["fichero_usuario"]["tmp_name"];
            $name = $_FILES["fichero_usuario"]["name"];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $fichero_subido = $miDir . "App/Uploads/" . $_POST["nombreArchivo"] . ".$ext";

            if ($ext == "csv")
            {
	            if (move_uploaded_file($tmp_name, "$fichero_subido"))
	            {
                    $csv = new DataCSV();
        
                    if ($csv->fromFile($fichero_subido) < 0)
                    {
    	                $msj = implode("|", $csv->getAtributes());
                    }
                    else
                    {
                        echo "4";
                    }
	            }
                else
                {
                    echo "3";
                }
            }
            else
            {
                echo "2";
            }
        }
        else
        {
            echo "1|" . $_FILES["fichero_usuario"]["error"] . "|";
        }
    }
    else
    {
        echo "0";
    }

    echo $msj;

?>
