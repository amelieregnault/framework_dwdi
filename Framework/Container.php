<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass;
use Closure;
use InvalidArgumentException;
use ReflectionMethod;
use ReflectionNamedType;

class Container
{

    private array $registry = [];

    public function set (string $name, Closure $function): void {
        $this->registry[$name] = $function;
    }

    public function get(string $className): object
    {
        if (array_key_exists($className, $this->registry)) {
            return $this->registry[$className]();
        }

        $reflector = new ReflectionClass($className);
        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $className;
        }

        $dependencies = $this->getDependencies($constructor, $className);
        return new $className(...$dependencies);
    }

    private function getDependencies(ReflectionMethod $constructor, string $className): array {
        
        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            
            $type = $parameter->getType();

            if ($type === null) {
                exit("Constructor parameter '{$parameter->getName()}' 
                     in the $className class has no type declaration");
            }

            if (!($type instanceof ReflectionNamedType)) {
                exit("Constructor parameter '{$parameter->getName()}'
                     in the $className class ");
            }

            if ($type->isBuiltIn()) {
                throw new InvalidArgumentException("Unable to resolve constructor parameter 
                    '{$parameter->getName()} of type $type 
                    in the $className class is an invalid type : '$type' 
                    - only single named types supported");
            }

            $dependencies[] = $this->get((string) $type);
        }


        return $dependencies;

    }
}