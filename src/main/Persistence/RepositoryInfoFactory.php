<?php

namespace RendyRobbani\PHP\Persistence;

use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Exception\AttributeNotFoundException;

final class RepositoryInfoFactory
{
	private function __construct()
	{
	}

	/**
	 * @param string $class
	 * @return RepositoryInfo
	 * @throws \ReflectionException
	 */
	public static function instance(string $class): RepositoryInfo
	{
		$reflectionClass = Application::getReflectionClass($class);
		if ($repositoryAttributes = $reflectionClass->getAttributes(Repository::class)) {
			/** @var Repository $repository */
			$repository = $repositoryAttributes[0]->newInstance();
			return new RepositoryInfo($class, Application::getEntityInfo($repository->entity));
		}
		throw new AttributeNotFoundException($class, Repository::class);
	}
}