<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Attribute\Persistence;

#[Persistence("tx_puck_domain_model_page_teaser")]
class PageTeaser extends AbstractEntity
{
	/**
	 * @var int
	 */
	public int $page;

	/**
	 * @var string
	 */
	public string $title = '';

	/**
	 * @var string
	 */
	public string $text = '';

	/**
	 * @var ObjectStorage<FileReference>|null
	 */
	public ObjectStorage|null $media = null;

	/**
	 * @var string
	 */
	public string $icon = '';

	/**
	 * @var string
	 */
	public string $parentTable;
}