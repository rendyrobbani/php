<?php

namespace RendyRobbani\PHP\Persistence;

final class FieldInfo
{
	/**
	 * @param \ReflectionProperty $property
	 * @param Column $column
	 * @param Id|null $id
	 */
	public function __construct(public \ReflectionProperty $property,
	                            public Column              $column,
	                            public Id|null             $id = null)
	{
	}
}