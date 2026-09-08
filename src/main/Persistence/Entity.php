<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Entity
{
	/**
	 * @param string $table
	 * @param string $engine
	 * @param string $charset
	 * @param string $collate
	 */
	public function __construct(public string $table,
	                            public string $engine = "innodb",
	                            public string $charset = "utf8mb4",
	                            public string $collate = "utf8mb4_unicode_ci")
	{
	}
}