<?php

include_once("Conexion.php");

linea_parada();
generarGruposJSON();
generarGrupoLineaJSON();

//Lee la DB y genera todos los JSON. Cada carpeta es un GRUPO.
function linea_parada()
{
    $mysqli = conectarDB();
    //Traigo todos los grupos
    $resultado = $mysqli->query("SELECT * from grupo WHERE 1");
    //Itero sobre cada grupo
    while ($grupo = $resultado->fetch_assoc())
    {
        $grupo_id = $grupo['grupo_id'];
        //Crea una carpeta con el ID del grupo
        mkdir("../json/$grupo_id", 0700) OR
                die("Problemas en la creacion de carpeta");
        //Traigo todas las líneas de cada grupo
        $query = "SELECT l.linea_id, l.numero, l.nombre FROM linea l
JOIN linea_grupo lg USING (linea_id)
JOIN grupo g USING (grupo_id)
WHERE g.numero=" . $grupo['numero'];
        $resultadoLineas = $mysqli->query($query);
        //Itero sobre cada línea
        while ($linea = $resultadoLineas->fetch_assoc())
        {
            $arrayParadas = [];
            $arrayRecorridos = [];
            $linea_id = $linea['linea_id'];
            unset($linea['linea_id']);
            $linea['grupo_numero'] = $grupo['numero'];
            $linea['grupo_nombre'] = $grupo['nombre'];

            //Itero sobre las paradas
            $resultadoParadas = $mysqli->query("SELECT latitud as lat, longitud as lng 
                FROM parada JOIN linea_parada USING (parada_id)
                WHERE linea_id=$linea_id ORDER BY posicion ASC");
            while ($parada = $resultadoParadas->fetch_assoc())
            {
                $arrayParadas[] = $parada;
            }

            //Itero sobre los recorridos
            $resultadoRecorridos = $mysqli->query("SELECT latitud as lat, longitud as lng 
                FROM recorrido WHERE linea_id=$linea_id");
            while ($recorrido = $resultadoRecorridos->fetch_assoc())
            {
                $arrayRecorridos[] = $recorrido;
            }
            $linea['paradas'] = $arrayParadas;
            $linea['recorrido'] = $arrayRecorridos;
            //Crea un JSON dentro de la carpeta con el ID de la linea
            $archivo = fopen("../json/$grupo_id/$linea_id.json", "w") or
                    die("Problemas en la creacion del JSON");
            fputs($archivo, json_encode($linea));
        }
    }
    $mysqli->close();
}

//Genera un archivo JSON para los grupos de Mendoza
function generarGruposJSON()
{
    $mysqli = conectarDB();
    $arrayGrupos = Array();
    //Traigo todos los grupos
    $resultado = $mysqli->query("SELECT grupo_id, nombre, numero, COUNT(*) as cantLineas FROM `grupo`
                    JOIN linea_grupo USING (grupo_id)
                    GROUP BY grupo_id");
    while ($grupo = $resultado->fetch_assoc())
    {
        $arrayGrupos[] = $grupo;
    }
    //Crea un JSON dentro de la carpeta con el ID de la linea

    $archivo = fopen("../json/grupos.json", "w") or
            die("Problemas en la creacion del JSON");
    fputs($archivo, json_encode($arrayGrupos));
    $mysqli->close();
}

//Genera un archivo JSON para cada grupo, con infomracion sobre las lineas
function generarGrupoLineaJSON()
{
    $mysqli = conectarDB();
    $arrayLineas = Array();
    //Traigo todos los grupos
    $resultado = $mysqli->query("SELECT grupo_id, nombre, numero, COUNT(*) as cantLineas FROM `grupo`
                    JOIN linea_grupo USING (grupo_id)
                    GROUP BY grupo_id");
    while ($grupo = $resultado->fetch_assoc())
    {
        $arrayLineas = Array();
        $grupo_id = $grupo['grupo_id'];
        //Traigo todas las lines de un grupo determinado
        $resultadoLineas = $mysqli->query("SELECT linea_id, numero, nombre FROM `linea`
                    JOIN linea_grupo USING (linea_id)
                    WHERE grupo_id= $grupo_id");
        while ($linea = $resultadoLineas->fetch_assoc())
        {
            $arrayLineas[] = $linea;
        }
        $archivo = fopen("../json/$grupo_id/lineas.json", "w") or
                die("Problemas en la creacion del JSON");
        fputs($archivo, json_encode($arrayLineas));
    }
    $mysqli->close();
}

//Función Recursiva. Recibe un path y devuelve un array de id_grupos.
//Cada elemento del array contiene un array de id_lineas
function generarArrayArchivos($path)
{
    $dir = opendir($path);
    $archivos = [];
    while ($archivo = readdir($dir))
    {
        if (is_dir("$path/$archivo") and $archivo <> ".." and $archivo <> ".")
        {
            $archivos[] = array('grupo' => $archivo,
                'lineas' => generarArrayArchivos("$path/$archivo"));
        }
        elseif (!is_dir($path . '/' . $archivo))
        {
            $nombre = explode('.', $archivo);
            $archivos[] = "$nombre[0]";
        }
    }

    closedir($dir);
    unset($dir);
    return $archivos;
}

?>
