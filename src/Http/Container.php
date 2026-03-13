<?php

declare(strict_types=1);

namespace Veloce\Http;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;

/**
 * DI-контейнер для автоматического управления зависимостями (Auto-wiring).
 */
class Container
{
    /** @var array<string, object> Реестр созданных экземпляров (Singleton-хранилище) */
    private array $instances = [];

    /** @var array<string, bool> Флаги для предотвращения бесконечной рекурсии */
    private array $resolving = [];

    /** @var array<string, string> Карта соответствия интерфейсов классам */
    private array $bindings = [];

    /**
     * Связывает абстракцию (интерфейс) с конкретной реализацией.
     */
    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Ручная регистрация готового экземпляра сервиса.
     * 
     * @param string $name Имя класса или интерфейса.
     * @param object $instance Объект сервиса.
     */
    public function set(string $name, object $instance): void
    {
        $this->instances[$name] = $instance;
    }

    /**
     * Проверяет, создан ли уже экземпляр данного класса.
     */
    public function has(string $name): bool
    {
        return isset($this->instances[$name]) || isset($this->bindings[$name]);
    }

    /**
     * Получает экземпляр класса, автоматически разрешая его зависимости.
     * 
     * @template T of object
     * @param class-string<T>|string $className Полное имя класса.
     * @return T
     * @throws RuntimeException Если класс не найден, имеет циклическую зависимость или не может быть создан.
     */
    public function get(string $className): object
    {
        if (isset($this->bindings[$className])) {
            $className = $this->bindings[$className];
        }

        if (isset($this->instances[$className])) {
            return $this->instances[$className];
        }

        if (isset($this->resolving[$className])) {
            throw new RuntimeException("Circular dependency detected for: {$className}");
        }

        try {
            $reflection = new ReflectionClass($className);

            if (!$reflection->isInstantiable()) {
                throw new RuntimeException("Class {$className} is not instantiable");
            }

            $this->resolving[$className] = true;
            $constructor = $reflection->getConstructor();

            if (null === $constructor) {
                unset($this->resolving[$className]);
                return $this->instances[$className] = new $className();
            }

            $dependencies = [];
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $dependencies[] = $this->get($type->getName());
                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    $paramName = $parameter->getName();
                    throw new RuntimeException("Unresolvable dependency [{$paramName}] in {$className}");
                }
            }

            unset($this->resolving[$className]);
            return $this->instances[$className] = $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException $e) {
            unset($this->resolving[$className]);
            throw new RuntimeException("Container error: " . $e->getMessage(), 0, $e);
        }
    }
}
