<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Text extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header;

    /**
     * @var string
     */
    public string $headerLayout;

    /**
     * @var string
     */
    public string $headerPosition;

    /**
     * @var string
     */
    public string $subheader;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $icon = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $layout;

    /**
     * @var string
     */
    public string $bodytext = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $containerWidth = 12;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $containerPosition = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $containerOffset = 0;


    /**
     * Content Element TCA
     */

    /**
     * @return string
     */
    public function showItem(): string
    {
        return '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;gridContainer,
        --palette--;;headers,
        --palette--;;bodytext,';
    }

    /**
     * @return array
     */
    public function columnsOverrides(): array
    {
        return [
            'layout' => [
              'config' => [

              ]
            ],
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                ],
            ]
        ];
    }
}