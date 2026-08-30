<?php

namespace RendyRobbani\PHP\Persistence;

final class EntityInfo
{
	/**
	 * @param string $class
	 * @param string $table
	 * @param FieldInfo[] $fields
	 * @param FieldInfo $fieldId
	 */
	public function __construct(public string    $class,
	                            public string    $table,
	                            public array     $fields,
	                            public FieldInfo $fieldId)
	{
	}
}