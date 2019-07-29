<?php set_time_limit(0); include("../conexion.php"); $fecha_hora = date('Y-m-d h:i:s');  ?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Ajuste de Precios ProductosG</title>

        <style type="text/css">
            .agregado { display: inline-block; padding: 8px; background: #9EE1FF; border: 1px #002B63 solid; margin: 5px; }
            .modificado { display: inline-block; padding: 8px; background: #B5EFA5; border: 1px #42563C solid; margin: 5px; }
            .error { display: inline-block; padding: 8px; background: #FF433D; border: 1px #771E1C solid; margin: 5px; }
        </style>
    </head>
<body>

<?php

/** Se incluye el Parche **/
set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');

/** Abrimos IOFactory como gestor de archivos .xls */
include 'PHPExcel/IOFactory.php';

/** Seleccionamos la ruta del archivo */
$inputFileName = 'PRECIOSura2.xls';
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
    $cantida_tmp = $rowData['0']['0'];
    $codigo_tmp = $rowData['0']['1'];
    $nombre_tmp = $rowData['0']['2'];
    $costo_tmp = $rowData['0']['3'];
    $precio_tmp = $rowData['0']['4'];
    $iva_temp = $rowData['0']['5']; 

    $costo_tmp_2 = number_format($costo_tmp, 2, ',', '');

    if ($costo_tmp == "0.00" OR $costo_tmp == "0.0" OR $costo_tmp == "0") {
        $costo_tmp = "1.00";
    }      

    if ($precio_tmp == "0.00" OR $precio_tmp == "0.0" OR $precio_tmp == "0") {
        $precio_tmp = "1.00";
    }        

    if ($row > 1) {
        $numregs_art = "0";

        $consulta3 = "SELECT * FROM articulos WHERE codigo_unico='$codigo_tmp'";
        $ejecutar_consulta3 = $conexion->query($consulta3);
        $numregs_art = $ejecutar_consulta3->num_rows;
        $registro3 = $ejecutar_consulta3->fetch_assoc();

        $precio_ori = $registro3["precio_actual"];

        $costo_ori = $registro3["costo_actual"];
        $costo_ori = number_format($costo_ori, 2, ',', '');

        $id_art = $registro3["id_articulo"];

        $consulta_cons_8 = "SELECT * FROM existencias WHERE id_articulo='$id_art'";
        $ejecutar_cons_8 = $conexion->query($consulta_cons_8);
        $registro_cons_8 = $ejecutar_cons_8->fetch_assoc();

        $cantidad_xy = $registro_cons_8["cantidad_xy"];
        $cantidad_xy = number_format($cantidad_xy, 2, ',', '');

        $iva_ori = $registro3["alicuota"];

        $iva_orig = $iva_temp;

        if ($iva_orig == "0,00" OR $iva_orig == "0" OR $iva_orig == "0.00") {
            $iva_orig = "0";
            $iva_fmt = "EXENTO";
        } elseif ($iva_orig == "8,00" OR $iva_orig == "8" OR $iva_orig == "8.00") {
            $iva_orig = "08";
            $iva_fmt = "IVA: 8%";
        } elseif ($iva_orig == "10,00" OR $iva_orig == "10" OR $iva_orig == "10.00") {
            $iva_orig = "10";
            $iva_fmt = "IVA: 10%";
        } elseif ($iva_orig == "12,00" OR $iva_orig == "12" OR $iva_orig == "12.00") {
            $iva_orig = "12";
            $iva_fmt = "IVA: 12%";
        } elseif ($iva_orig == "16,00" OR $iva_orig == "16" OR $iva_orig == "16.00") {
            $iva_orig = "16";
            $iva_fmt = "IVA: 16%";
        }

        $ali_iva = "1.".$iva_orig;

        $utilidad = (1-($costo_tmp/$precio_tmp))*100;
        $utilidad = number_format($utilidad, 2, ',', '.');
        $utilidad_fmt = "Utilidad: ".$utilidad."%";

        $prcio = $precio_tmp * $ali_iva;

        $pre_dif = $precio_tmp - $precio_ori; 

        $cost_dif = $costo_tmp * 1;

        $subprecio_c = number_format($prcio, 2, ',', '.');
        $subprecio_c = "Bs. ".$subprecio_c;

        $costo_frm = number_format($cost_dif, 2, ',', '.');
        $costo_frm = "Bs. ".$costo_frm;

        if ($numregs_art == "0") {                   

            $consulta = "INSERT INTO articulos (descripcion,codigo_unico,costo_actual,precio_actual,alicuota) VALUES ('$nombre_tmp','$codigo_tmp','$costo_tmp','$precio_tmp','$iva_temp')";
            
            if ($ejecutar_consulta = $conexion->query($consulta)) {                
                $id_nva_marca = $conexion->insert_id;                   

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','1','$fecha_hora','ID: $id_nva_marca','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','5','$fecha_hora','$codigo_tmp','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','4','$fecha_hora','$nombre_tmp','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','6','$fecha_hora','$costo_frm','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','8','$fecha_hora','$iva_fmt','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','21','$fecha_hora','$utilidad_fmt','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);

                $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','7','$fecha_hora','$subprecio_c','SISTEMA')";
                $ejecutar_consulta = $conexion->query($consulta_op);                

                echo "<div class='agregado'>Agregado: <b>$nombre_tmp</b> > $precio_tmp</div>";                  
            } else {
                echo "<div class='error'>";
                printf("$nombre_tmp Errormessage: %s\n", $conexion->error);  
                echo "</div>";                   
            }

            if ($cantida_tmp != '0,00') {               

                $consulta_cons_2 = "SELECT * FROM existencias WHERE id_articulo='$id_nva_marca' AND indice='1'";
                $ejecutar_cons_2 = $conexion->query($consulta_cons_2);
                $num_cons_2 = $ejecutar_cons_2->num_rows;

                if ($num_cons_2 == '0') {
                    $consulta_ajust = "INSERT INTO existencias (id_articulo,id_almacen,cantidad_xy,fecha_ult,indice) VALUES ('$id_nva_marca','3','$cantida_tmp','$fecha_hora','1')";
                    if ($ejecutar_ajust = $conexion->query($consulta_ajust)) {
                        $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_nva_marca','9','$fecha_hora','$cantida_tmp','SISTEMA')";
                        $ejecutar_consulta = $conexion->query($consulta_op);  
                    }
                }
            }


        } else {

            echo "$costo_tmp_2 y $costo_ori <br>";

            if ($costo_tmp_2 != $costo_ori) {

                $consulta_mod = "UPDATE articulos SET costo_actual='$costo_tmp' WHERE codigo_unico='$codigo_tmp'";

                if ($ejecutar_consulta = $conexion->query($consulta_mod)) {

                    $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_art','11','$fecha_hora','$costo_frm','SISTEMA')";
                    $ejecutar_consulta = $conexion->query($consulta_op);
                }
            }  

            if ($iva_orig != "$iva_temp" AND $iva_orig != "") {
                $consulta_iva = "UPDATE articulos SET alicuota='$iva_temp' WHERE codigo_unico='$codigo_tmp'";

                if ($ejecutar_consulta_iva = $conexion->query($consulta_iva)) {
                    echo "<div class='modificado'>Modificado IVA: <b>$nombre_tmp</b> > $iva_ori -> $iva_temp </div>";               
                }
            }

            if ($pre_dif > 5 OR $pre_dif < -5) {             
     
                $consulta_mod = "UPDATE articulos SET precio_actual='$precio_tmp' WHERE codigo_unico='$codigo_tmp'";

                if ($ejecutar_consulta = $conexion->query($consulta_mod)) {              
                    echo "<div class='modificado'>Modificado: <b>$nombre_tmp</b> > $precio_ori -> $precio_tmp </div>";                   

                    $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_art','21','$fecha_hora','$utilidad_fmt','SISTEMA')";
                    $ejecutar_consulta = $conexion->query($consulta_op);   

                    $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_art','12','$fecha_hora','$subprecio_c','SISTEMA')";
                    $ejecutar_consulta = $conexion->query($consulta_op);  
                                           
                }
            }            

            if ($cantida_tmp != $cantidad_xy) {

                $consulta_cons_2 = "SELECT * FROM existencias WHERE id_articulo='$id_art' AND indice='1'";
                $ejecutar_cons_2 = $conexion->query($consulta_cons_2);
                $num_cons_2 = $ejecutar_cons_2->num_rows;

                if ($num_cons_2 == '0') {
                    $consulta_ajust = "INSERT INTO existencias (id_articulo,id_almacen,cantidad_xy,fecha_ult,indice) VALUES ('$id_art','3','$cantida_tmp','$fecha_hora','1')";
                    if ($ejecutar_ajust = $conexion->query($consulta_ajust)) {
                        $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_art','9','$fecha_hora','$cantida_tmp','SISTEMA')";
                        $ejecutar_consulta = $conexion->query($consulta_op);  
                    }

                } else {
                    $consulta_ajust = "UPDATE existencias SET cantidad_xy='$cantida_tmp' WHERE id_articulo='$id_art'";

                    if ($ejecutar_ajust = $conexion->query($consulta_ajust)) {
                        $consulta_op = "INSERT INTO tabla_operaciones (id_articulo,tipo_de_operacion,fecha_hora,detalle,usuario) VALUES ('$id_art','15','$fecha_hora','$cantida_tmp','SISTEMA')";
                        $ejecutar_consulta = $conexion->query($consulta_op);  
                    }

                }

            }

        }        
        
    }

}


?>
<body>
</html>