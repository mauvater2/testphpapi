<?php
/**
 *
 * @About:      API Interface
 * @File:       index.php
 * @Date:       $Date:$ Feb-2018
 * @Version:    $Rev:$ 1.0
 * @Developer:  Mauricio Vater (mauvater2@gmail.com)
 **/

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"'); 

include_once '../include/Config.php';

/*
	En caso de utilizar una DB, descomentar la linea 29 
	y eliminar el include_once del file Config ya que el mismo está incluido en DBHandler.
*/

//require_once '../include/DbHandler.php'; 

require '../libs/Slim/Slim.php'; 
\Slim\Slim::registerAutoloader(); 
$app = new \Slim\Slim();

$app->get('/localizacion', function() {
    
    $response = array();
    //$db = new DbHandler();

    /* 
	Array de localizaciones para ejemplo response. 
	Se puede usar el resultado de un query a la base de datos mediante un metodo en DBHandler
    */
    $localizaciones = array( 
						array('pais'=>'Argentina', 'provincia'=>'Buenos Aires', 'ciudad'=>'Las Flores'),
						array('pais'=>'Estados Unidos', 'estado'=>'Michigan', 'condado'=>'Wayne', 'municipio_ciudad'=>'Detroit')
					);
    
    $response["error"] = false;
    $response["message"] = "Países cargados: " . count($localizaciones);
    $response["localizaciones"] = $localizaciones;

    echoResponse(200, $response);
});


$app->post('/localizacion', 'authenticate', function() use ($app) {

    verifyRequiredParams(array('pais','nivel'));

    $response = array();
    
	/*
	filtramos segun si es nivel 4 (paises con estado-condado-municipio_ciudad) 
	y capturamos los parametros recibidos para almacenarlos como un nuevo array
	*/
	
	if($app->request->post('nivel') != '4'){
		$param['pais']  = $app->request->post('pais');
		$param['provincia'] = $app->request->post('provincia');
		$param['ciudad']  = $app->request->post('ciudad');
		
	}
	else
	{
		$param['pais']  = $app->request->post('pais');
		$param['estado'] = $app->request->post('estado');
		$param['condado']  = $app->request->post('condado');
		$param['municipio_ciudad']  = $app->request->post('municipio_ciudad');
	}
	
    
    /* Podemos inicializar la conexion a la base de datos si queremos hacer uso de esta para procesar los parametros con DB */
    //$db = new DbHandler();

    /* Podemos crear un metodo que almacene la nueva localizacion, por ejemplo: */
    //$localizacion = $db->createLocalizacion($param);

    if ( is_array($param) ) {
        $response["error"] = false;
        $response["message"] = "Localizacion creada satisfactoriamente!";
        $response["localizacion"] = $param;
    } else {
        $response["error"] = true;
        $response["message"] = "Error al crear la localizacion. Por favor intenta nuevamente.";
    }
    echoResponse(201, $response);
});

$app->run();


/*********************** FUNCTIONS **************************************/

function verifyRequiredParams($required_fields) {
    
	$error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        
        $app->stop();
    }
}
 

function echoResponse($status_code, $response) {
    
	$app = \Slim\Slim::getInstance();
    
	$app->status($status_code);
	$app->contentType('application/json');
 
    echo json_encode($response);
}


function authenticate(\Slim\Route $route) {
    
	$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
 
    if (isset($headers['Authorization'])) {
        
        $token = $headers['Authorization'];
        
        if (!($token == API_KEY)) { 
            
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);
            
            $app->stop();
            
        } else {
            //proceed
        }
    } else {
		
        $response["error"] = true;
        $response["message"] = "Falta token de autorización";
        echoResponse(400, $response);
        
        $app->stop();
    }
}
?>