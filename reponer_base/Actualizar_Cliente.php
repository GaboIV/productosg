<?php set_time_limit(0); include("../conexion.php"); ?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Ajuste de Precios ProductosG</title>
    </head>
<body>

<?php

/** Se incluye el Parche **/
set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');

/** Abrimos IOFactory como gestor de archivos .xls */
include 'PHPExcel/IOFactory.php';

/** Seleccionamos la ruta del archivo */
$inputFileName = 'CLIENTES.xls';
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);


//  Read your Excel workbook
try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
} catch(Exception $e) {
    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

//  Get worksheet dimensions
$sheet = $objPHPExcel->getSheet(0); 
$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

$porcadauno = 100 / $highestRow;

echo "  <div style='width:500px; background: #f1f1f1!important; padding: 5px; height: 32px;'>
            <div id='temp_agabe' style='text-align:center; width:0%; background: white; height: 22px;'></div>
        </div><br><br>";

//  Loop through each row of the worksheet in turn
for ($row = 1; $row <= $highestRow; $row++){ 

    $completado = ($row * $porcadauno);
    $complet = intval($completado);
    $compor = $complet."%";
    echo "<script type='text/javascript'>document.getElementById('temp_agabe').style.width = '$compor'; document.getElementById('temp_agabe').innerHTML = '$compor';</script>";    

    //  Read a row of data into an array
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                                    NULL,
                                    TRUE,
                                    FALSE);
    $cedula_tmp = $rowData['0']['0'];
    $nombre_tmp = $rowData['0']['1'];
    $direccion_tmp = $rowData['0']['2'];
    $telefono_tmp = $rowData['0']['3'];

    if ($row > 1) {
        $numregs_art = "0";

        if ($cedula_tmp[0] == "E" OR $cedula_tmp[0] == "V") {
        	$consulta3 = "SELECT * FROM clientes WHERE documento='$cedula_tmp'";
       		$ejecutar_consulta3 = $conexion->query($consulta3);
          $numregs_art = $ejecutar_consulta3->num_rows; 

       		if ($numregs_art == "0") {
       			$consulta = "INSERT INTO clientes (documento,nombre,direccion,telefono1) VALUES ('$cedula_tmp','$nombre_tmp','$direccion_tmp','$telefono_tmp')";
            
           		if ($ejecutar_consulta = $conexion->query($consulta)) {
           			echo "Agregada PERSONA: <b>$cedula_tmp</b> > $nombre_tmp<br>";                  
	            } else {
	                printf("$nombre_tmp Errormessage: %s\n", $conexion->error);                     
	            }           		  
       		}
        } elseif ($cedula_tmp[0] == "G" OR $cedula_tmp[0] == "J") {
        	$consulta3 = "SELECT * FROM clientes WHERE documento='$cedula_tmp'";
       		$ejecutar_consulta3 = $conexion->query($consulta3);
          $numregs_art = $ejecutar_consulta3->num_rows; 

       		if ($numregs_art == "0") {
       			$consulta = "INSERT INTO clientes (documento,nombre,direccion,telefono1) VALUES ('$cedula_tmp','$nombre_tmp','$direccion_tmp','$telefono_tmp')";
            
           		if ($ejecutar_consulta = $conexion->query($consulta)) {
           			echo "Agregada EMPRESA: <b>$cedula_tmp</b> > $nombre_tmp<br>";                  
	            } else {
	                printf("$nombre_tmp Errormessage: %s\n", $conexion->error);                     
	            }           		  
       		}
        }     
    }
}
?>
<body>
</html>