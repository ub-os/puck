<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;


trait ContainerChild {

    /**
     * @var int
     */
    public int $txContainerParent = 0;

    /**
     * @var mixed|null
     * @Transient
     */
    protected mixed $containerParent = null;

    // todo - get container parent data

    /**
     * @return mixed
     */
    public function getContainerParent(): mixed {
        if ($this->containerParent === null) {
            // get parent container data from database and set it to $this->containerParent
            // can't use extbase repository because we do not know the container class
        }
        return $this->containerParent;
    }
}