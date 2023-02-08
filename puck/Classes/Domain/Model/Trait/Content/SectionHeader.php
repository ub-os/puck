<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use HDNET\Autoloader\Annotation\DatabaseField;

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
     * @DatabaseField ("int")
     */
    public int $headerSpacingOverride = 0;

    /**
     * @var string
     */
    public string $subheader = '';
    /**
     * @var ?bool
     * @Transient
     */
    protected ?bool $hasHeaderSpacing = null;

    /**
     * @return bool
     */
    public function getHasHeaderSpacing(): bool
    {
        if ($this->hasHeaderSpacing === null) {
            $this->hasHeaderSpacing = (!$this->header || $this->headerLayout > 29) && !$this->headerSpacingOverride;
        }
        return $this->hasHeaderSpacing;
    }

    /**
     * @param bool $hasHeaderSpacing
     * @return void
     */
    public function setHasHeaderSpacing(bool $hasHeaderSpacing): void
    {
        $this->hasHeaderSpacing = $hasHeaderSpacing;
    }
}