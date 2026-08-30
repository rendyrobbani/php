<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Column
{
	public function __construct(public string|null $name = null,
	                            public string|null $type = null,
	                            public string|null $size = null)
	{
	}
}