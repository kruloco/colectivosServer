<?php

include_once("Conexion.php");
//echo file_get_contents("http://colectivo.site90.net/controladores/controladorGrupo.php");

$grupos = getGrupos();
// Si es una petición cross-domain o normal
if (isset($_GET['callback']))
{
    echo $_GET['callback'] . "($grupos)";
}
else
{
    echo $grupos;
}

//Devuelve JSON de todos los grupos (id, nombre, numero, cantLineas)
function getGrupos()
{
    $mysqli = conectarDB();
    $resultado = $mysqli->query("SELECT grupo_id, nombre, numero, COUNT(*) as cantLineas FROM `grupo`
                    JOIN linea_grupo USING (grupo_id)
                    GROUP BY grupo_id");

    while ($grupo = $resultado->fetch_assoc())
        $arrayGrupos[] = $grupo;
    //$arrayGrupos[] = array_map('utf8_encode', $grupo);
    $mysqli->close();

    return json_encode($arrayGrupos);
}

?>
