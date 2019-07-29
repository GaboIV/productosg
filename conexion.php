<?php
	date_default_timezone_set('America/Caracas');
	function conectarse() {		
		$servidor = "localhost"; $usuario = "root"; $password = ""; $bd = "ventasg";
		$conectar = new mysqli($servidor, $usuario, $password, $bd);
		return $conectar; }
	$conexion = conectarse();
?>