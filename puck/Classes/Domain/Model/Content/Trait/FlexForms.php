<?php
namespace UBOS\Puck\Domain\Model\Content\Trait;

use ReflectionClass;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puckloader\Attribute\FlexFormProperty;

trait FlexForms {

    /**
     * @var ?array<array>
     * @Transient
     */
    protected ?array $flexForms = null;

    /**
     * @return array
     */
    public function getFlexForms(): array
    {
        if ($this->flexForms === null) {
            $refClass = new ReflectionClass($this::class);
            $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
            foreach ($refClass->getProperties() as $property) {
                $refFlexFormProperty = $property->getAttributes(FlexFormProperty::class);
                if (count($refFlexFormProperty) > 0) {
                    $name = $property->getName();
                    $this->flexForms[$name] = $flexformService->convertFlexFormContentToArray($this->$name);
                    if ($this->typeZeroStringsAsInteger) {
                        $this->flexForms[$name] = PuckUtility::convertZeroStringsToInteger($this->flexForms[$name]);
                    }
                }
            }
        }
        return $this->flexForms;
    }

    protected bool $typeZeroStringsAsInteger = true;

    /**
     * @param array $flexForms
     * @return void
     */
    public function setFlexForms(array $flexForms): void
    {
        $this->flexForms = $flexForms;
    }

}