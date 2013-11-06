<?php

include_once("Conexion.php");
if (isset($_GET['linea'], $_GET['callback']))
{
    $lineaId = $_GET['linea'];

    $arrayColectivos = getColectivos($lineaId);
	
	if (empty($arrayColectivos))
    $colectivos['status'] = 'SinColectivo';
else
{
    $colectivos['status'] = 'OK';
	$colectivos['colectivos'] = $arrayColectivos;;
}
    echo $_GET['callback'] . '(' . json_encode($colectivos) . ')';
}

//Recibe el id de una línea
//Busca en la DB todos los colectivos que pretenezcan a esa línea
//Devuelve JSON de todos los colectivos (id, lat, lng)
function getColectivos($lineaId)
{
    $mysqli = conectarDB();
	$arrayColectivos=Array();
    $resultado = $mysqli->query("SELECT colectivo_id, latitud lat, longitud lng FROM `colectivo`
                    WHERE linea_id=$lineaId");

    while ($colectivo = $resultado->fetch_assoc())
    //    $arrayColectivos[] = $colectivo;
    $arrayColectivos[] = array_map('utf8_encode', $colectivo);
    $mysqli->close();

    return $arrayColectivos;
}

?>
