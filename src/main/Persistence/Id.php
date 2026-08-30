<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Id
{
	/**
	 * @param bool $isGeneratedValue
	 */
	public function __construct(public bool $isGeneratedValue = false)
	{
	}
}