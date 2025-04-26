<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Ruta para subir archivos de presentaciones
$routes->post('/subirArchivo', 'ControladorPresentaciones::subirArchivo');

// Ruta para crear Usuarios
$routes->post('/crearUsuario', 'ControladorUsuario::crearUsuario');
$routes->post('/verificarOCrearUsuario','ControladorUsuario::verificarOCrearUsuario') ;

// Ruta para crear Presentacion
$routes->post('/crearPresentacion', 'ControladorPresentaciones::crearPresentacion');

// Mensajes
$routes->post('/crearMensaje', 'ControladorMensaje::crearMensaje');

// Obtener presentación por ID 
$routes->get('/obtenerPresentacion/(:segment)', 'ControladorPresentaciones::obtenerPresentacion/$1');

// Obtener todas las presentaciones de un usuario
$routes->get('/presentacionesUsuario/(:segment)', 'ControladorPresentaciones::obtenerPresentacionesPorUsuario/$1');

// Obtener presentación por ID pero en controladorTelegram
$routes->get('/obtenerPresentacionTelegramUrl/(:any)', 'ControladorTelegram::obtenerURLPresentacion/$1');


//visor
$routes->get('/visor/(:segment)', 'ControladorTelegram::verPresentacion/$1');

//Funciones necesarias para enviar comandos o info del archivo a desplegar
$routes->post('/enviarComandoDesdeWeb', 'ControladorTelegram::enviarComandoDesdeWeb');
$routes->options('/enviarComandoDesdeWeb', 'ControladorTelegram::enviarComandoDesdeWeb');

$routes->post('/enviarPresentacionDesdeWeb', 'ControladorTelegram::enviarPresentacionDesdeWeb');
$routes->options('/enviarPresentacionDesdeWeb', 'ControladorTelegram::enviarPresentacionDesdeWeb');

//Se obtiene los mensajes del usuario por id
$routes->get('mensajes/(:any)', 'ControladorMensaje::obtenerMensajesPorUsuario/$1');

//Prueba
$routes->get('probar-getupdates', 'ControladorTelegram::manejarMensajesTelegram');

//Sirve para forzar Cors y poder obtener la presentacion a desplegar en el visor
$routes->get('/verArchivo/(:any)', 'ControladorPresentaciones::verArchivo/$1');


//Sirve para forzar Cors y poder obtener la presentacion a desplegar en el visor pero con nombre codificado
$routes->get('verArchivoBase64/(:any)', 'ControladorPresentaciones::verArchivoBase64/$1');

//Basicamente sirve para autenticar el usuario usando el widget login de TELEGRAM
$routes->post('verificarLoginTelegram', 'ControladorUsuario::verificarLoginTelegram');

