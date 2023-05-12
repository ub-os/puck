<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use HDNET\Autoloader\Annotation\DatabaseField;

trait ContainerLayout {
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $containerWidth = 12;

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $containerPosition = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $containerOffset = 0;
}