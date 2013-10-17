<?php

include_once("Conexion.php");
if (isset($_GET['lat'], $_GET['lng'], $_GET['id'], $_GET['callback']))
{
    $latitud = $_GET['lat'];
    $longitud = $_GET['lng'];
    $idColectivo = $_GET['id'];

    $valor = actualizarPosicion($latitud, $longitud, $idColectivo);
    echo $_GET['callback'] . '(' . json_encode($valor) . ')';
}

//Recibe una coordenada y un id de Colectivo
//Busca en la DB ese colectivo y actualiza su posición
//Devuelve true o false
function actualizarPosicion($lat, $lng, $idColectivo)
{
    $mysqli = conectarDB();
    $retorno = false;
    
    $consulta = "UPDATE colectivo SET latitud=$lat, longitud=$lng WHERE colectivo_id=$idColectivo;";
    $mysqli->query($consulta);
    if ($mysqli->affected_rows)
    {
        $retorno = true;
    }

    $mysqli->close();
    return $retorno;
}

?>
