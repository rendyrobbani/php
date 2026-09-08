<?php

namespace RendyRobbani\PHP\Persistence;

final class UniqueKey
{
	/**
	 * @param array $columns
	 */
	public function __construct(public array $columns)
	{
	}
}