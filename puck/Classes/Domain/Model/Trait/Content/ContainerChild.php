<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use HDNET\Autoloader\Annotation\DatabaseField;

trait ContainerChild {
    public int $txContainerParent = 0;

    protected mixed $containerParent = null;

    // todo - get container parent data
    public function getContainerParent(): mixed {
        if ($this->containerParent === null) {
            // get parent container data from database and set it to $this->containerParent
            // can't use extbase repository because we do not know the container class
        }
        return $this->containerParent;
    }
}