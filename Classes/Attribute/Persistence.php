<?php
declare(strict_types=1);

namespace UBOS\Puck\Attribute;
use Attribute;

/**
 * Used for automatic Extbase persistence mapping configuration.
 * Add to model classes to map them to database tables, or to properties to map them to table fields.
 * @see AttributeReflection::createExtbasePersistenceMapping() uses this attribute for persistence mapping
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class Persistence
{
	/**
	 * @param string $name Table name when used on a class, or field name when used on a property
	 */
	public function __construct(
		public string $name,
	)
	{
	}
}
