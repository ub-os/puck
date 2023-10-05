<?php

namespace UBOS\Puck\Hooks\WebLayoutHeader;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use UBOS\Puck\Domain\Model\Page\NewsPage;
use UBOS\Puck\Domain\Repository\PageRepository;

class PageHeader extends AbstractHeader
{
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getPropertyPermissions(): array
    {
        $class = NewsPage::class;
        $dataMap = GeneralUtility::makeInstance(ObjectManager::class)->get(DataMapper::class)->getDataMap($class);
        $backendUserAuthentication = $this->getBackendUser();

        $permissions = [];
        foreach (GeneralUtility::makeInstance(\ReflectionClass::class, $class)->getProperties() ?? [] as $reflection) {
            if ($property = $reflection->name) {
                $permissions[$property] = ($columnMap = $dataMap->getColumnMap($property)) && ((($table = $columnMap->getChildTableName()) && $backendUserAuthentication->check('tables_select', $table)) || $backendUserAuthentication->check('non_exclude_fields', $dataMap->getTableName() . ':' . $columnMap->getColumnName()));
            }
        }

        return $permissions;
    }

    /** @throws AspectNotFoundException */
    public function render(): string
    {
        if ((int)($this->row['doktype'] ?? 0) === PageRepository::DOKTYPES['news']) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/WebLayoutHeader/NewsPage.html', [
                'page' => $dataMapper->map('UBOS\Puck\Domain\Model\Page\NewsPage', [$this->row])[0],
                'row' => $this->row,
                'propertyPermissions' => $this->getPropertyPermissions()
            ])->render();
        }
        if ((int)($this->row['doktype'] ?? 0) === PageRepository::DOKTYPES['person']) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/WebLayoutHeader/PersonPage.html', [
                'page' => $dataMapper->map('UBOS\Puck\Domain\Model\Page\PersonPage', [$this->row])[0],
                'row' => $this->row,
                'propertyPermissions' => $this->getPropertyPermissions()
            ])->render();
        }
        return '';
    }
}