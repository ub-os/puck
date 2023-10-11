<?php

namespace UBOS\Puck\Domain\Repository;

use TYPO3\CMS\Core\Domain\Repository\PageRepository as CorePageRepository;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use UBOS\Puck\Menu\Trait\Repository\FindByMenuDemand;

class PageRepository extends Repository
{
    use FindByMenuDemand;

    public const DOKTYPES = [
        'default' => CorePageRepository::DOKTYPE_DEFAULT,
        'shortcut' => CorePageRepository::DOKTYPE_SHORTCUT,
        'link' => CorePageRepository::DOKTYPE_LINK,
        'sysfolder' => CorePageRepository::DOKTYPE_SYSFOLDER,
        'mountpoint' => CorePageRepository::DOKTYPE_MOUNTPOINT,
        'spacer' => CorePageRepository::DOKTYPE_SPACER,

        'start' => 16501,
        'news' => 16503,
        'person' => 16504,
        'plugin' => 16511,
    ];
    public const DEFAULT_ALLOWED_TYPES = [
        self::DOKTYPES['default'],
        self::DOKTYPES['shortcut'],
        self::DOKTYPES['link'],
        self::DOKTYPES['start'],
        self::DOKTYPES['person'],
        self::DOKTYPES['plugin'],
    ];
    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    protected array $allowedTypes = self::DEFAULT_ALLOWED_TYPES;
    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }
    /**
     * @param array $allowedTypes
     */
    public function setAllowedTypes(array $allowedTypes): self
    {
        $this->allowedTypes = $allowedTypes;
        return $this;
    }

    public function setPageObjectType(string $className): void
    {
        $this->objectType = $className;
    }
    /**
     * @return void
     */
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function additionalMenuDemandConstraints(QueryInterface $query, array $settings): array
    {
        $constraints = [
            $query->in('doktype', $this->allowedTypes),
            $query->logicalNot($query->equals('uid', $settings['currentPageId'] ?? 0))
        ];
        if ($settings['navHide']) {
            $constraints[] = $query->equals('nav_hide', 0);
        }
        if ($settings['author']) {
            $constraints[] = $query->equals('post_author', $settings['author']);
        }
        return $constraints;

    }

}