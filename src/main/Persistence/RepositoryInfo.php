<?php

namespace RendyRobbani\PHP\Persistence;

final class RepositoryInfo
{
	/**
	 * @param string $class
	 * @param EntityInfo $entityInfo
	 */
	public function __construct(public string $class,
	                            public EntityInfo $entityInfo)
	{
	}
}