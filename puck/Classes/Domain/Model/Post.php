<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
/**
 * @DatabaseTable("pages")
 */
class Post extends Page
{
    public const DOKTYPE = 60;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $publishDate = '';
}