<?php

namespace UBOS\Puck\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

/**
 * Routing aspect mapper that maps the value to itself.
 */
class NothingMapper implements StaticMappableAspectInterface
{
	public function generate(string $value): ?string
	{
		return $value;
	}

	public function resolve(string $value): ?string
	{
		return $value;
	}
}