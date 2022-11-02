<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("02_menu")
 */
class MenuAnchors extends AbstractEntity
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
     */
    public string $layout;

    /**
     * @var string
     */
    public string $bodytext = '';


    /**
     * Computed properties.
     */

    /**
     * @var array
     */
    public array $anchors = [];

    /**
     * @return void
     */
    public function setAnchors(): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $records = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('theme_anchor')),
                $queryBuilder->expr()->eq('pid', $this->pid),
            )
            ->add('orderBy', 'sorting ASC')
            ->execute()
            ->fetchAll();
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $dataMapper = $objectManager->get(DataMapper::class);
        $this->anchors = $dataMapper->map('UBOS\\Theme\\Domain\\Model\\Content\\Anchor', $records);
    }

    /**
     * @return void
     */
    public function computeProperties(): void
    {
        $this->setAnchors();
    }


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
        --palette--;;layout,
        --palette--;;headers,';
    }

    /**
     * @return array
     */
    public function columnsOverrides(): array
    {
        return [];
    }
}