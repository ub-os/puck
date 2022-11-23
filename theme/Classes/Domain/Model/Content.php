<?php

namespace UBOS\Theme\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use HDNET\Autoloader\Annotation\DatabaseTable;

/**
 * @DatabaseTable("tt_content")
 */
class Content extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header;
}