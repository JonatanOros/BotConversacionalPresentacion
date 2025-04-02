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

// Ruta para crear Presentacion
$routes->post('/crearPresentacion', 'ControladorPresentaciones::crearPresentacion');

// Mensajes
$routes->post('/crearMensaje', 'ControladorMensaje::crearMensaje');
