<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use HDNET\Autoloader\Annotation\DatabaseField;

trait FlexForm {

    /**
     * @var string
     */
    public string $piFlexform = '';

    /**
     * @var ?array
     * @Transient
     */
    public ?array $flexformArray = null;

    /**
     * @return array
     */
    public function getFlexformArray(): array
    {
        if ($this->flexformArray === null) {
            $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
            $this->flexformArray = $flexformService->convertFlexFormContentToArray($this->piFlexform);
        }
        return $this->flexformArray;
    }

    /**
     * @param array $flexformArray
     * @return void
     */
    public function setFlexformArray(array $flexformArray): void
    {
        $this->flexformArray = $flexformArray;
    }
}