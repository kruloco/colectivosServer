<?php

include_once("Conexion.php");
$lineas = getLineas();

if (isset($_GET['callback']))
{
    echo $_GET['callback'] . "($lineas)";
}
else
{
    echo $lineas;
}

//Devulve JSON de todas las lineas (id, nombre, numero)
function getLineas()
{
    $grupoId = $_GET['idGrupo'];
    $mysqli = conectarDB();
    $resultado = $mysqli->query("SELECT linea_id, nombre, numero FROM `linea`
                    JOIN linea_grupo USING (linea_id)
                    WHERE grupo_id=$grupoId");

    while ($linea = $resultado->fetch_assoc())
        $arrayLineas[] = $linea;
    //$arrayLineas[] = array_map('utf8_encode', $linea);
    $mysqli->close();

    return json_encode($arrayLineas);
}

?>
