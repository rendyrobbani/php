<?php

namespace RendyRobbani\PHP\Persistence;

final class ForeignKey
{
	/**
	 * @param array $columns
	 * @param string $referenceTable
	 * @param array $referenceColumns
	 */
	public function __construct(public array  $columns,
	                            public string $referenceTable,
	                            public array  $referenceColumns)
	{
	}
}