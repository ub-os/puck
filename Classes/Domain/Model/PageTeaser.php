<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Attribute\Persistence;

/**
 * Model for 'tx_puck_domain_model_page_teaser'
 * used in @see \UBOS\Puck\Controller\PageMenuController to override teaser defaults of page menu items
 */
#[Persistence("tx_puck_domain_model_page_teaser")]
class PageTeaser extends AbstractEntity
{
	public int $page;

	public string $title = '';

	public string $text = '';

	/**
	 * @var ObjectStorage<FileReference>|null
	 */
	public ObjectStorage|null $media = null;

	public string $icon = '';

}