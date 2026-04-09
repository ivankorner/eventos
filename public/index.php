<?php
/**
 * Front Controller — punto de entrada único de la aplicación
 * Todas las requests HTTP pasan por aquí gracias al .htaccess
 */

// Cargar autoloader de Composer (vendors + classmap propio)
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Cargar configuración de la aplicación (define constantes, carga .env)
require_once dirname(__DIR__) . '/config/app.php';

// Registrar el manejador de errores centralizado
ErrorHandler::register();

// Cargar la base de datos (singleton PDO)
require_once dirname(__DIR__) . '/config/database.php';

// Cargar el router
require_once dirname(__DIR__) . '/config/routes.php';

// Iniciar sesión
Session::start();

// Instanciar el router y registrar las rutas
$router = new Router();
require_once dirname(__DIR__) . '/routes/web.php';

// Despachar la request al controlador correspondiente
$router->dispatch();
