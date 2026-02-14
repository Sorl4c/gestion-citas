<?php

namespace App;

use FastRoute;

class Router {
    public static function dispatch() {
        $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', 'App\Controllers\HomeController@index');
            $r->addRoute('GET', '/api/v1/availability', 'App\Controllers\Api\AvailabilityController@index');
            $r->addRoute('POST', '/api/v1/appointments', 'App\Controllers\Api\AppointmentController@create');
            $r->addRoute('POST', '/api/v1/appointments/cancel', 'App\Controllers\Api\AppointmentController@cancel');
            
            // Public Views
            $r->addRoute('GET', '/cancel', 'App\Controllers\CancellationController@index');

            // Auth Routes
            $r->addRoute('POST', '/api/v1/auth/login', 'App\Controllers\Api\AuthController@login');
            $r->addRoute('POST', '/api/v1/auth/logout', 'App\Controllers\Api\AuthController@logout');
            $r->addRoute('GET', '/api/v1/auth/check', 'App\Controllers\Api\AuthController@check');
            
            // Admin Views (Frontend)
            $r->addRoute('GET', '/admin', 'App\Controllers\AdminController@index');
            
            // Admin API (Protected)
            $r->addRoute('GET', '/api/v1/admin/appointments', 'App\Controllers\Api\AdminApiController@getAppointments');
            $r->addRoute('POST', '/api/v1/admin/appointments/{id}/cancel', 'App\Controllers\Api\AdminApiController@cancel');
            $r->addRoute('PATCH', '/api/v1/admin/appointments/{id}/status', 'App\Controllers\Api\AdminApiController@updateStatus');
            $r->addRoute('PUT', '/api/v1/admin/appointments/{id}', 'App\Controllers\Api\AdminApiController@update');
        });

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        // Handle subdirectory deployment logic
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        // Normalize slashes for consistency
        $scriptDir = str_replace('', '/', $scriptDir);
        
        // If the URI starts with the script directory, strip it
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }
        
        // Ensure URI starts with /
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo '404 Not Found';
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                http_response_code(405);
                echo '405 Method Not Allowed';
                break;
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                
                list($class, $method) = explode('@', $handler);
                
                if (class_exists($class)) {
                    $controller = new $class();
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $vars);
                    } else {
                         echo "Method $method not found in $class";
                    }
                } else {
                    echo "Controller class $class not found";
                }
                break;
        }
    }
}