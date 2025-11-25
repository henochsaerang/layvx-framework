<?php

namespace App\Core;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use Exception;

class Container {
    /**
     * The container's shared instances.
     * @var array
     */
    protected $instances = [];

    /**
     * The container's bindings.
     * @var array
     */
    protected $bindings = [];

    /**
     * Register a binding with the container.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Register a shared binding in the container.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null) {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @return mixed
     * @throws Exception
     */
    public function resolve($abstract) {
        // If an instance already exists for a singleton, return it.
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the binding resolver.
        $concrete = $this->getConcrete($abstract);

        // Build the instance.
        $object = $this->build($concrete);

        // If the binding is shared (a singleton), store the instance.
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete($abstract) {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }
        return $abstract;
    }

    /**
     * Check if a binding is shared.
     *
     * @param string $abstract
     * @return bool
     */
    protected function isShared($abstract) {
        return isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared'] === true;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param Closure|string $concrete
     * @return mixed
     * @throws Exception
     */
    protected function build($concrete) {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param ReflectionParameter[] $dependencies
     * @return array
     * @throws Exception
     */
    protected function resolveDependencies(array $dependencies) {
        $results = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();

            if (!$type || $type->isBuiltin()) {
                if ($dependency->isDefaultValueAvailable()) {
                    $results[] = $dependency->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve un-typed parameter \${$dependency->getName()} in class {$dependency->getDeclaringClass()->getName()}");
                }
            } else {
                $results[] = $this->resolve($type->getName());
            }
        }

        return $results;
    }
}
