<?php

namespace App\Core;

use App\Core\Request;
use App\Core\Response;
use Exception;
use ReflectionMethod;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
    ];

    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function get(string $uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, $action) {
        $this->routes[$method][$uri] = $action;
    }
    
    public function loadRoutes(string $file) {
        // A bit of a trick to make the static Route class available to the routes file
        $router = $this;
        require $file;
        return $this;
    }

    public function dispatch() {
        $uri = $this->request->uri();
        $method = $this->request->method();

        foreach ($this->routes[$method] as $route => $action) {
            // Convert route to regex: /users/{id} -> /users/([a-zA-Z0-9_]+)
            $regex = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $route);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                // Get associative array of route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $this->callAction($action, $params);
            }
        }

        // If no route was matched
        return Response::view('errors.404', [], 404);
    }

    protected function callAction($action, array $params) {
        if (is_callable($action)) {
            return call_user_func($action, ...array_values($params));
        }

        if (is_array($action) && count($action) >= 2) {
            $controllerName = $action[0];
            $methodName = $action[1];
            $fullyQualifiedControllerName = 'App\\Controllers\\' . $controllerName;

            if (class_exists($fullyQualifiedControllerName) && method_exists($fullyQualifiedControllerName, $methodName)) {
                $controller = app()->resolve($fullyQualifiedControllerName);
                
                // Inject parameters into controller method
                $response = $this->injectDependencies($controller, $methodName, $params);
                
                return $response;
            }
        }

        throw new Exception("Route action is not valid.");
    }
    
    protected function injectDependencies($controller, $methodName, array $routeParams) {
        $reflector = new ReflectionMethod($controller, $methodName);
        $methodParams = $reflector->getParameters();
        $args = [];

        foreach ($methodParams as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if (isset($routeParams[$paramName])) {
                // Matched from route parameter by name
                $args[] = $routeParams[$paramName];
            } elseif ($paramType && !$paramType->isBuiltin()) {
                 // It's a class, try to resolve from container
                $className = $paramType->getName();
                if ($className === Request::class) {
                    $args[] = $this->request; // Inject the current request object
                } else {
                    $args[] = app()->resolve($className);
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new Exception("Could not resolve dependency: \\\${$paramName}");
            }
        }
        
        return $controller->{$methodName}(...$args);
    }
}
