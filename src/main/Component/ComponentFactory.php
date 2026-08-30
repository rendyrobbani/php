<?php

namespace RendyRobbani\PHP\Component;

use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Configuration\Configuration;
use RendyRobbani\PHP\Exception\AttributeNotFoundException;
use RendyRobbani\PHP\Exception\ImplementationClassNotFoundException;
use RendyRobbani\PHP\Exception\UnresolvableConstructorParameterException;

final class ComponentFactory
{
	private function __construct()
	{
	}

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 * @throws \ReflectionException
	 */
	public static function instance(string $class)
	{
		$reflectionClass = Application::getReflectionClass($class);
		if ($reflectionClass->getAttributes(Component::class) === []) throw new AttributeNotFoundException($class, Component::class);
		return $reflectionClass->isInterface() ? self::instanceInterface($reflectionClass) : self::instanceClass($reflectionClass);
	}

	/**
	 * @template T
	 * @param \ReflectionClass $reflectionClass
	 * @return T
	 * @throws \ReflectionException
	 */
	public static function instanceClass(\ReflectionClass $reflectionClass)
	{
		if ($constructor = $reflectionClass->getConstructor()) {
			$dependencies = [];
			foreach ($constructor->getParameters() as $parameter) {
				if ($configurationAttributes = $parameter->getAttributes(Configuration::class)) {
					/** @var Configuration $configuration */
					$configuration = $configurationAttributes[0]->newInstance();
					$dependencies[] = Application::getConfig($configuration->key);
				} else {
					$type = $parameter->getType();
					if ($type->isBuiltin()) {
						if ($parameter->isDefaultValueAvailable()) {
							$dependencies[] = $parameter->getDefaultValue();
							continue;
						}
						throw new UnresolvableConstructorParameterException($parameter);
					}
					$dependencies[] = Application::getComponent($type->getName());
				}
			}
			return $reflectionClass->newInstanceArgs($dependencies);
		}
		return $reflectionClass->newInstance();
	}

	/**
	 * @template T
	 * @param \ReflectionClass $reflectionClass
	 * @return T
	 * @throws \ReflectionException
	 */
	public static function instanceInterface(\ReflectionClass $reflectionClass)
	{
		$classImpl = $reflectionClass->getName() . "Impl";
		if (class_exists($classImpl)) return self::instanceClass(Application::getReflectionClass($classImpl));
		throw new ImplementationClassNotFoundException($classImpl);
	}
}