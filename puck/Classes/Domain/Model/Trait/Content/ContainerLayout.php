<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use UBOS\Puckloader\Attribute\ModelColumn;

trait ContainerLayout {
    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $containerWidth = 12;

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $containerPosition = '';

    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $containerOffset = 0;
}