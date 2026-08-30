<?php

namespace RendyRobbani\PHP\Code;

use RendyRobbani\PHP\Persistence\FieldInfo;

final readonly class RepositoryImplCodeInfo
{
	/**
	 * @param FieldInfo $field
	 * @param string $info
	 */
	public function __construct(public FieldInfo $field,
	                            public string    $info)
	{
	}
}