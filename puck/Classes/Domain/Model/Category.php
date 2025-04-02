<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use UBOS\Puck\Attribute\Persistence;

#[Persistence("sys_category")]
class Category extends AbstractEntity
{
	/**
	 * @var string
	 */
	public string $title = '';

	/**
	 * @var ObjectStorage<Category>|null
	 */
	#[Lazy]
	protected ?ObjectStorage $parent = null;

	/**
	 * @var string
	 */
	public string $slug = '';

	public function getParent(): ?Category
	{
		return $this->parent?->current();
	}
}