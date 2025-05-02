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
$routes->options('/obtenerPresentacion/(:segment)', 'ControladorPresentaciones::obtenerPresentacion/$1');

// Obtener todas las presentaciones de un usuario
$routes->get('/presentacionesUsuario/(:segment)', 'ControladorPresentaciones::obtenerPresentacionesPorUsuario/$1');
$routes->options('/presentacionesUsuario/(:segment)', 'ControladorPresentaciones::obtenerPresentacionesPorUsuario/$1');

// Obtener presentación por ID pero en controladorTelegram
$routes->get('/obtenerPresentacionTelegramUrl/(:any)', 'ControladorTelegram::obtenerURLPresentacion/$1');
$routes->options('/obtenerPresentacionTelegramUrl/(:any)', 'ControladorTelegram::obtenerURLPresentacion/$1');


//visor
$routes->get('/visor/(:segment)', 'ControladorTelegram::verPresentacion/$1');
$routes->options('/visor/(:segment)', 'ControladorTelegram::verPresentacion/$1');

//Funciones necesarias para enviar comandos o info del archivo a desplegar
$routes->post('/enviarComandoDesdeWeb', 'ControladorTelegram::enviarComandoDesdeWeb');
$routes->options('/enviarComandoDesdeWeb', 'ControladorTelegram::enviarComandoDesdeWeb');

$routes->post('/enviarPresentacionDesdeWeb', 'ControladorTelegram::enviarPresentacionDesdeWeb');
$routes->options('/enviarPresentacionDesdeWeb', 'ControladorTelegram::enviarPresentacionDesdeWeb');

//Se obtiene los mensajes del usuario por id
$routes->get('mensajes/(:any)', 'ControladorMensaje::obtenerMensajesPorUsuario/$1');

//Prueba
$routes->get('probar-getupdates', 'ControladorTelegram::manejarMensajesTelegram');

//webhook para telegram, asi se podra responer a los mensajes
$routes->post('/responderA-MensajesDeTelegram','ControladorTelegram::manejarMensajesTelegram');


//Sirve para forzar Cors y poder obtener la presentacion a desplegar en el visor
$routes->get('/verArchivo/(:any)', 'ControladorPresentaciones::verArchivo/$1');


//Sirve para forzar Cors y poder obtener la presentacion a desplegar en el visor pero con nombre codificado
$routes->get('/verArchivoBase64/(:any)', 'ControladorPresentaciones::verArchivoBase64/$1');
$routes->options('/verArchivoBase64/(:any)', 'ControladorPresentaciones::verArchivoBase64/$1');

//Basicamente sirve para autenticar el usuario usando el widget login de TELEGRAM
$routes->post('/verificarLoginTelegram', 'ControladorUsuario::verificarLoginTelegram');
$routes->options('/verificarLoginTelegram', 'ControladorUsuario::verificarLoginTelegram');

//se pide info para ver si esta logeado
$routes->get('/usuarioLogueado', 'ControladorUsuario::usuarioLogueado');
$routes->options('/usuarioLogueado', 'ControladorUsuario::usuarioLogueado');

//se obtiene el id del usuario por medio de la sesion
$routes->get('/obtenerUsuario', 'ControladorUsuario::obtenerUsuario');
$routes->options('/obtenerUsuario', 'ControladorUsuario::obtenerUsuario');


//Elimina Una presentacion
$routes->delete('/eliminarPresentacion/(:segment)', 'ControladorPresentaciones::eliminarPresentacion/$1');
$routes->options('/eliminarPresentacion/(:segment)', 'ControladorPresentaciones::eliminarPresentacion/$1');


//Cierra sesion
$routes->post('/cerrarSesion', 'ControladorUsuario::cerrarSesion');
$routes->options('/cerrarSesion', 'ControladorUsuario::cerrarSesion');


