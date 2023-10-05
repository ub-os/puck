<?php
namespace UBOS\Puck\Domain\Model\Content\Trait;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use UBOS\Puckloader\Attribute\ModelColumn;

trait SectionHeader {
    /**
     * @var string
     */
    public string $header = '';

    /**
     * @var string
     */
    public string $headerLayout = '';

    /**
     * @var string
     */
    public string $headerPosition = '';

    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $headerSpacingOverride = 0;

    /**
     * @var string
     */
    public string $subheader = '';
    /**
     * @var ?bool
     * @Transient
     */
    protected ?bool $hasNoHeaderSpacing = null;

    /**
     * @return bool
     */
    public function getHasNoHeaderSpacing(): bool
    {
        if ($this->hasNoHeaderSpacing === null) {
            $this->hasNoHeaderSpacing = (!$this->header || $this->headerLayout > 29) && !$this->headerSpacingOverride;
        }
        return $this->hasNoHeaderSpacing;
    }

    /**
     * @param bool $hasNoHeaderSpacing
     * @return void
     */
    public function setHasNoHeaderSpacing(bool $hasNoHeaderSpacing): void
    {
        $this->hasNoHeaderSpacing = $hasNoHeaderSpacing;
    }
}