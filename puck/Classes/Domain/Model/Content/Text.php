<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Text extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header;

    /**
     * @var string
     */
    public string $headerLayout;

    /**
     * @var string
     */
    public string $headerPosition;

    /**
     * @var int
     * @DatabaseField ("int")
     */
    public int $headerSpacingOverride;

    /**
     * @var string
     */
    public string $subheader;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $icon = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $layout;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $frameClass = '';

    /**
     * @var string
     */
    public string $bodytext = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $containerWidth = 12;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $containerPosition = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $containerOffset = 0;

    /**
     * @var ?bool
     * @Transient
     */
    protected ?bool $disableHeaderSpacing = null;

    /**
     * @return bool
     */
    public function getDisableHeaderSpacing(): bool
    {
        if ($this->disableHeaderSpacing === null) {
            $this->disableHeaderSpacing = (!$this->header || $this->headerLayout > 29) && !$this->headerSpacingOverride;
        }
        return $this->disableHeaderSpacing;
    }

    /**
     * @param bool $disableHeaderSpacing
     * @return void
     */
    public function setDisableHeaderSpacing(bool $disableHeaderSpacing): void
    {
        $this->disableHeaderSpacing = $disableHeaderSpacing;
    }
}