<?php

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | Don't show ANY in production environments. Instead, let the system catch
 | it and display a generic error message.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
//error_reporting(E_ALL & ~E_DEPRECATED);

//este es necesario para ver el error mas arriba es para que no sea asi
error_reporting(-1);

// If you want to suppress more types of errors.
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

//ini_set('display_errors', '0');

//esto es para poder ver el error
ini_set('display_errors', '1');

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. It's not widely used currently, and may not survive
 | release of the framework.
 */
defined('CI_DEBUG') || define('CI_DEBUG', false);
