<?php

function conectarDB()
{
    $mysqli = new mysqli("localhost", "a5932039_carim", "colectivo123", "a5932039_col");
    //$mysqli = new mysqli("mysql3.000webhost.com", "a5932039_carim", "colectivo123", "a5932039_col");
    
    if (mysqli_connect_errno())
    {
        printf("Error de conexión: %s\n", mysqli_connect_error());
        exit();
    }
    return $mysqli;
}

function pre($variable)
{
    echo '<pre>';
    print_r($variable);
    echo '</pre>';
}

?>
