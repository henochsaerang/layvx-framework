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

    /**
     * A stack to hold middleware for nested route groups.
     * @var array
     */
    protected array $groupMiddlewareStack = [];

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function get(string $uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param array $attributes
     * @param Closure $callback
     */
    public function group(array $attributes, \Closure $callback)
    {
        // Push middleware to the group stack
        $middleware = $attributes['middleware'] ?? [];
        if (!empty($middleware)) {
            $this->groupMiddlewareStack[] = $middleware;
        }

        // Execute the callback to define routes within the group
        $callback($this);

        // Pop middleware from the group stack
        if (!empty($middleware)) {
            array_pop($this->groupMiddlewareStack);
        }
    }

    protected function addRoute(string $method, string $uri, $action) {
        $groupMiddleware = !empty($this->groupMiddlewareStack)
            ? end($this->groupMiddlewareStack)
            : [];

        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => is_array($groupMiddleware) ? $groupMiddleware : [$groupMiddleware],
        ];
    }
    
    public function loadRoutes(string $file): self {
        // A bit of a trick to make the static Route class available to the routes file
        $router = $this;
        require $file;
        return $this;
    }

    /**
     * Find the route matching the current request.
     * 
     * @return array|null
     */
    public function dispatch(): ?array {
        $uri = $this->request->uri();
        $method = $this->request->method();

        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $data) {
            // Convert route to regex: /users/{id} -> /users/(?P<id>[a-zA-Z0-9_]+)
            $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $route);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                // Get associative array of route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return [
                    'action' => $data['action'],
                    'middleware' => $data['middleware'],
                    'params' => $params,
                ];
            }
        }

        // If no route was matched
        return null;
    }

    public function callAction($action, array $params) {
        if (is_callable($action)) {
            return call_user_func($action, ...array_values($params));
        }

        if (is_array($action) && count($action) >= 2) {
            $controllerName = $action[0];
            $methodName = $action[1];
            
            if (class_exists($controllerName)) {
                $fullyQualifiedControllerName = $controllerName;
            } else {
                $fullyQualifiedControllerName = 'App\\Controllers\\' . $controllerName;
            }

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
