<?php

//Inicio de sesión a partir de peticiones http
// header( 'Content-Type: application/json' );

// if ( !array_key_exists( 'HTTP_X_TOKEN', $_SERVER ) ) {

// 	die;
// }

// $url = 'https://'.$_SERVER['HTTP_HOST'].'/auth';

// $ch = curl_init( $url );
// curl_setopt( $ch, CURLOPT_HTTPHEADER, [
// 	"X-Token: {$_SERVER['HTTP_X_TOKEN']}",
// ]);
// curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
// $ret = curl_exec( $ch );

// if ( curl_errno($ch) != 0 ) {
// 	die ( curl_error($ch) );
// }

// if ( $ret !== 'true' ) {
// 	http_response_code( 403 );

// 	die;
// }

//Definimos los recursos disponibles
$allowedResourceTypes = [
    'books',
    'authors',
    'genres',
];
//Validamos que el recurso este disponible
$resourceType = $_GET['resource_type'];

if (!in_array($resourceType, $allowedResourceTypes)){
    http_response_code( 400 );
    die;
}

//Defino los recursos
$books = [
    1 => [
        'titulo' => 'DRACULA',
        'id_autor' => 2,
        'id_genero' => 2,
    ],
    2 => [
        'titulo' => 'El profesor',
        'id_autor' => 1,
        'id_genero' => 1,
    ],
    3 => [
        'titulo' => 'Un Final Perfecto',
        'id_autor' => 1,
        'id_genero' => 1,
    ],
];

//Aviso al cliente que se está enviando json
header( 'Content-Type: application/json');

//Levantamos el id del recurso buscado
$resourceId = array_key_exists('resource_id', $_GET) ? $_GET['resource_id'] : '';

//Generamos la respuesta asumiedo que el pedido es correcto
switch( strtoupper ($_SERVER['REQUEST_METHOD'])) {
    case 'GET':
        if (empty($resourceId)) {
            echo json_encode( $books );            
        } else {
            if( array_key_exists( $resourceId, $books)){
                echo json_encode( $books[$resourceId] );
            } else {
                http_response_code(404);
            }
        }

        break;
    case 'POST':

        $json = file_get_contents('php://input');
        
        $books[] = json_decode($json, true);

        // echo array_keys( $books )[ count($books) - 1 ];
        echo json_encode( $books );

        break;
    case 'PUT':
        //Validamos que el recurso buscado exista
        if (!empty($resourceId) && array_key_exists($resourceId, $books)) {
            //Tomamos la entrada cruda
            $json = file_get_contents('php://input');

            //Transformamos el json recibido a un nuevo elemento del arreglo
            $books[ $resourceId ] = json_decode( $json, true );

            //Retornamos la colección modificada en formato JSON
            echo json_encode( $books );
        }

        break;
    case 'DELETE':
        //Validamos que el recurso exista
        if (!empty($resourceId) && array_key_exists($resourceId, $books)) {
            //Eliminamos el recurso
            unset( $books[ $resourceId ] ); //Elimina una clave de un arreglo
        }

        echo json_encode( $books );

        break;
}