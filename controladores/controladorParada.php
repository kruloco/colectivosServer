<?php

include_once("Conexion.php");
$Origenlat = $_GET['Olat'];
$Origenlng = $_GET['Olng'];
$Destinolat = $_GET['Dlat'];
$Destinolng = $_GET['Dlng'];

$arrayOrigen = getParadasCercanas($Origenlat, $Origenlng, 500);
$arrayDestino = getParadasCercanas($Destinolat, $Destinolng, 500);

//$arrayOrigen = getParadasCercanas(-32.8909663, -68.84350549999999, 100);
//$arrayDestino = getParadasCercanas(-32.8759265, -68.8449008, 100);
//pre(count($arrayDestino));
//pre(count($arrayOrigen));
//pre((limpiarLineasArray($arrayDestino,$arrayOrigen))) ;

if (empty($arrayOrigen))
    $lineas['status'] = 'SinOrigen';
else if (empty($arrayDestino))
    $lineas['status'] = 'SinDestino';
else
{
//PRIORIDAD AL DESTINO
    $lineas['lineas'] = limpiarLineasArray($arrayOrigen, $arrayDestino);
    $lineas['status'] = 'OK';
}

if (isset($_GET['callback']))
{
    echo $_GET['callback'] . '(' . json_encode($lineas) . ')';
}
else
{
    echo json_encode($lineas);
}

//Recibe 2 Arrays y borra del PRIMERO las líneas que no están en ambos
////En otra palabras, le da PRIORIDAD a las paradas del SEGUNDO array
//Devuelve el primer array modificado
function limpiarLineasArray($arrayNuevo, $array)
{
    foreach ($arrayNuevo as $indice => $nuevo)
    {
//        $borrar = false;
        $mantener = busquedaBinaria($nuevo['linea_id'], $array, 0, count($array));
//        foreach ($array as $value)
//        {
//            if ($nuevo['linea_id'] == $value['linea_id'])
//            {
//                $borrar = false;
//                break;
//            }
//        }
        if ($mantener == false)
            unset($arrayNuevo[$indice]);
    }
    return $arrayNuevo;
}

//recibe una coordenada y un radio y busca las paradas dentro de ese radio
//Si no encuentra, agranda el radio +100 y vuelve a buscar hasta que encuentre
//Devuelve array de todos las LINEAS (linea_id, grupo_id, nombre, numero)
function getParadasCercanas($lat, $lng, $metros)
{
    $arrayLineas = Array();
    $mysqli = conectarDB();
    $filas = 0;

    //Mientras no haya resultados, repetir la consulta
    //Límite: radio de 1000 metros, para no hacer bucle infinito
    while ($filas == 0 && $metros < 1000)
    {
        $algoritmoHaversine = "SELECT linea_id, grupo_id, linea.nombre, linea.numero,
        min( ROUND( 1000 * ( acos( sin( radians($lat) ) * sin( radians( latitud ) ) + cos( radians($lat) ) * cos( radians( latitud ) ) * cos( radians($lng) - radians( longitud ) ) ) *6378 ) , 6 ) ) AS distancia
        FROM parada
        JOIN linea_parada USING ( parada_id )
        JOIN linea USING ( linea_id )
        JOIN linea_grupo USING ( linea_id )
        JOIN grupo USING ( grupo_id )
        GROUP BY linea_id HAVING distancia < $metros
        ORDER BY linea_id ASC";

        $resultado = $mysqli->query($algoritmoHaversine);
        $filas = $resultado->num_rows;
        $metros = $metros + 100;
    }

    while ($linea = $resultado->fetch_assoc())
        $arrayLineas[] = $linea;
//$arrayLineas[] = array_map('utf8_encode', $linea);
    $mysqli->close();
    return $arrayLineas;

//    $plano = "SELECT parada_id, linea_id, grupo_id, linea.nombre, linea.numero
//        MIN( ROUND( 1000 * SQRT( 
//        POW((longitud - ($lng)) , 2 ) + 
//        POW((latitud - ($lat)) , 2 ) ) , 4 )) AS distancia
//        FROM parada
//        JOIN linea_parada USING ( parada_id )
//        JOIN linea USING ( linea_id )
//        JOIN linea_grupo USING ( linea_id )
//        JOIN grupo USING ( grupo_id )
//        GROUP BY linea_id
//        ORDER BY distancia ASC LIMIT 0 , 500";
}

//Búsqueda binaria recursiva para 'linea_id'
//Devuelve TRUE si encuentra el elemento
function busquedaBinaria($key, $array, $inicio, $fin)
{
// Selección de la posición del elemento central.
    $pivote = (int) ($inicio + ($fin - $inicio) / 2);
// Condición de corte.
    if ($inicio >= $fin)
        return FALSE;
    if ($array[$pivote]['linea_id'] > $key)
    {
        return busquedaBinaria($key, $array, $inicio, $pivote - 1);
    }
    else if ($array[$pivote]['linea_id'] < $key)
    {
        return busquedaBinaria($key, $array, $pivote + 1, $fin);
    }
    else
    {
        return TRUE;
    }
}

?>
