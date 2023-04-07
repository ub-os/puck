<?php

declare(strict_types=1);

namespace UBOS\Puck\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ContentElementWizard
{
    /**
     * @var array
     */
    public $tab;

    public $order;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
        if (isset($values['order'])) {
            $this->order = $values['order'];
        }
        if (isset($values['tab'])) {
            $this->tab = $values['tab'];
        } elseif (isset($values['value'])) {
            $this->tab = $values['value'];
        }
    }
}
