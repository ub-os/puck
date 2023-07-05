<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use ReflectionClass;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use UBOS\Puck\Attribute\FlexFormProperty;

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
                        $this->flexForms[$name] = $this->convertZeroStringsToInteger($this->flexForms[$name]);
                    }
                }
            }

        }
        return $this->flexForms;
    }

    protected bool $typeZeroStringsAsInteger = true;

    protected function convertZeroStringsToInteger(array|string $value): array|string|int
    {
        if (is_array($value)) {
            $array = [];
            foreach($value as $key => $value) {
                $array[$key] = $this->convertZeroStringsToInteger($value);
            }
            return $array;
        } else if ($value === '0') {
            return 0;
        }
        return $value;
    }

    /**
     * @param array $flexForms
     * @return void
     */
    public function setFlexForms(array $flexForms): void
    {
        $this->flexForms = $flexForms;
    }

}