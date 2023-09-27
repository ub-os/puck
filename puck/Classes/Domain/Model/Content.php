<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;


#[ModelPersistence("tt_content")]
class Content extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header;
}