<?php

namespace RendyRobbani\PHP;

use RendyRobbani\PHP\Component\ComponentFactory;
use RendyRobbani\PHP\Exception\ConfigValueNotFoundException;
use RendyRobbani\PHP\File\FileReader;
use RendyRobbani\PHP\Persistence\EntityInfo;
use RendyRobbani\PHP\Persistence\EntityInfoFactory;
use RendyRobbani\PHP\Persistence\RepositoryInfo;
use RendyRobbani\PHP\Persistence\RepositoryInfoFactory;

class Application
{
	/**
	 * @var array<string, mixed>
	 */
	private static array $config = [];

	/**
	 * @param string $configuration_file_json
	 * @return void
	 * @throws \ReflectionException
	 */
	public static function setConfig(string $configuration_file_json): void
	{
		self::$config = self::getComponent(FileReader::class)->readJSON($configuration_file_json, true);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public static function getConfig(string $key): mixed
	{
		$value = self::$config;
		foreach (explode(".", $key) as $path) {
			if (isset($value[$path])) {
				$value = $value[$path];
				continue;
			}
			throw new ConfigValueNotFoundException($key);
		}
		return $value;
	}

	/**
	 * @template T
	 * @var array<class-string<T>, \ReflectionClass>
	 */
	private static array $reflectionClasses = [];

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return \ReflectionClass
	 * @throws \ReflectionException
	 */
	public static function getReflectionClass(string $class): \ReflectionClass
	{
		if (!isset(self::$reflectionClasses[$class])) self::$reflectionClasses[$class] = new \ReflectionClass($class);
		return self::$reflectionClasses[$class];
	}

	/**
	 * @template T
	 * @var array<class-string<T>, T>
	 */
	private static array $components = [];

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @param T $component
	 * @return void
	 */
	public static function setComponent(string $class, $component): void
	{
		self::$components[$class] = $component;
	}

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 * @throws \ReflectionException
	 */
	public static function getComponent(string $class)
	{
		if (!isset(self::$components[$class])) self::$components[$class] = ComponentFactory::instance($class);
		return self::$components[$class];
	}

	/**
	 * @var array<class-string, EntityInfo>
	 */
	private static array $entityInfos = [];

	/**
	 * @param string $class
	 * @return EntityInfo
	 * @throws \ReflectionException
	 */
	public static function getEntityInfo(string $class): EntityInfo
	{
		if (!isset(self::$entityInfos[$class])) self::$entityInfos[$class] = EntityInfoFactory::instance($class);
		return self::$entityInfos[$class];
	}

	/**
	 * @var array<class-string, RepositoryInfo>
	 */
	private static array $repositoryInfos = [];

	/**
	 * @param string $class
	 * @return RepositoryInfo
	 * @throws \ReflectionException
	 */
	public static function getRepositoryInfo(string $class): RepositoryInfo
	{
		if (!isset(self::$repositoryInfos[$class])) self::$repositoryInfos[$class] = RepositoryInfoFactory::instance($class);
		return self::$repositoryInfos[$class];
	}
}