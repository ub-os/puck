<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Attribute\Persistence;

/**
 * Model for 'tx_puck_domain_model_person'
 */
#[Persistence("tx_puck_domain_model_person")]
class Person extends AbstractEntity
{
	public string $name = '';

	public string $slug = '';

	public int $isTeamMember = 0;

	public string $description = '';

	public string $position = '';

	public string $email = '';

	public string $phone = '';

	public string $link = '';

	public string $linkLinkedin = '';

	public string $linkXing = '';

	/**
	 * @var ObjectStorage<Page>|null
	 * @Lazy
	 */
	public ObjectStorage|null $pages = null;

	/**
	 * @var ObjectStorage<FileReference>|null
	 */
	public ObjectStorage|null $assets = null;

}