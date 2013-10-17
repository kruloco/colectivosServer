<?php

include_once("Conexion.php");
set_time_limit(0);
guardarRecorridosEnDB(0, 420);

//buscadorDeId(0, 420);
//Hace peticiones de archivos JSON a una página, los parsea y guarda en una DB
function guardarRecorridosEnDB($desde, $hasta) {
    $mysqli = conectarDB();

    $id = $desde;
    $contadorRecorridos = 0;
    while ($id < $hasta) { //420 es el ultimo ID    
        $id++;
        $json = file_get_contents("../jsonOriginales/$id.json");
        if ($json != false) {
            $contadorRecorridos++;
            $datos = json_decode($json, true);
            $nro_grupo = ltrim($datos["grupo"], 'G0');
            $nro_linea = html_entity_decode($datos["recorrido"]);
            $nombre_linea = html_entity_decode($datos["nombre"]);

            echo "<br> $id - $nro_grupo - $nro_linea $nombre_linea";
            //}}}
            $mysqli->query("INSERT INTO linea (numero,nombre) VALUES ('$nro_linea', '$nombre_linea')");
            $linea_id = $mysqli->insert_id;
            $mysqli->query("INSERT INTO linea_grupo (linea_id,grupo_id) VALUES ($linea_id, $nro_grupo)");

//Itero sobre los RECORRIDOS
            //Lo hago así xq hay recorridos con coordenadas repetidas y además están ordenadas
            $sql = '';
            foreach ($datos["route"] as $value) {
                $posicion = explode(",", $value);
                $latitud = trim($posicion[0]);
                $longitud = trim($posicion[1]);
                $sql.= "($latitud, $longitud, $linea_id),";
            }
            $consulta = "INSERT INTO recorrido (latitud, longitud, linea_id) VALUES $sql";
            $consultaFinal = rtrim($consulta, ',') . ';';
            $mysqli->query($consultaFinal);

//Itero sobre las PARADAS
            $paradas = [];
            foreach ($datos["stops"] as $value) {
                $latitud = trim($value['lat']);
                $longitud = trim($value['lng']);
                //Existe esa (lat, lng) ???
                $resultado = $mysqli->query("SELECT parada_id FROM parada WHERE latitud=$latitud AND longitud=$longitud");
                $id_temp = $resultado->fetch_assoc();
                //Si existe, obtengo id y guardo sólo la relación
                if ($id_temp) {
                    $parada_id = $id_temp['parada_id'];
                }
                //Si no existe, creo el registro y guardo la relación
                else {
                    $mysqli->query("INSERT INTO parada (latitud, longitud) VALUES ($latitud,$longitud)");
                    $parada_id = $mysqli->insert_id;
                }
                $paradas[] = $parada_id;
            }
            $consulta = "INSERT INTO linea_parada (linea_id, parada_id, posicion) VALUES ";
            $posicion = 1;
            foreach ($paradas as $value) {
                $consulta .= "($linea_id, $value, $posicion),";
                $posicion++;
            }
            $consultaFinal = rtrim($consulta, ',') . ';';
            $mysqli->query($consultaFinal);
        }
    }
    echo "<br>Se insertaron $contadorRecorridos recorridos en la DB";
    $mysqli->close();
}

//Hace peticiones GET al sitio web y genera un array con los ID que sí existen
//Esta función se usó una sola vez para saber cuántas peticiones hacer en la OTRA función (420)
function buscadorDeId($desde, $hasta) {
    $id = $desde;

    $arrayRecorridos = array();
    $errores = 0;
    while ($id < $hasta) {
        $id++;
        $json = file_get_contents("http://www.ciudaddemendoza.gov.ar/mapas/routes/get/$id");
        if ($json == 'error')
            $errores++;
        else {
            //array_push($arrayRecorridos, $id);
            $archivo = fopen("../jsonOriginales/$id.json", "w") or
                    die("Problemas en la creacion del JSON");
            fputs($archivo, $json);
        }
    }
    print_r(count($arrayRecorridos));
    echo "<br><br>Desde el $desde hasta el $hasta existen $errores peticiones erroneas";
}

?>
