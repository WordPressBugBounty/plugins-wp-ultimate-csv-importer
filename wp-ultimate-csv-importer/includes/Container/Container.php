<?php
namespace Smackcoders\UCI\Core\Container;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lightweight PSR-11 compliant Dependency Injection Container for WP Ultimate CSV Importer.
 */
class Container {
    
    private $bindings = [];
    private $instances = [];
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Register a class or closure.
     */
    public function bind($id, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $id;
        }
        $this->bindings[$id] = $concrete;
    }

    /**
     * Register a singleton instance.
     */
    public function singleton($id, $concrete = null) {
        $this->bind($id, function($c) use ($concrete, $id) {
            static $object;
            if (is_null($object)) {
                $object = $c->build($concrete ?: $id);
            }
            return $object;
        });
    }

    /**
     * Get an instance from the container.
     */
    public function get($id) {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->bindings[$id])) {
            return $this->build($id);
        }

        $concrete = $this->bindings[$id];

        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        $this->instances[$id] = $object;
        return $object;
    }

    /**
     * Instantiates a class via reflection (auto-wiring).
     */
    private function build($concrete) {
        try {
            $reflector = new \ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new \Exception("Target class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target class [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function getDependencies($parameters) {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();
            if ($dependency === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}");
                }
            } else {
                $dependencies[] = $this->get($dependency->name);
            }
        }
        return $dependencies;
    }
}
