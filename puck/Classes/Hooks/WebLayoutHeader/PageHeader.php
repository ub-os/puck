<?php

namespace UBOS\Puck\Hooks\WebLayoutHeader;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use UBOS\Puck\Domain\Repository\PageRepository;

class PageHeader extends AbstractHeader
{
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }


    /** @throws AspectNotFoundException */
    public function render(): string
    {
        if ((int)($this->row['doktype'] ?? 0) === PageRepository::DOKTYPES['news']) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/WebLayoutHeader/NewsPage.html', [
                'page' => $dataMapper->map('UBOS\Puck\Domain\Model\Page\NewsPage', [$this->row])[0],
                'row' => $this->row,
            ])->render();
        }
        if ((int)($this->row['doktype'] ?? 0) === PageRepository::DOKTYPES['person']) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/WebLayoutHeader/PersonPage.html', [
                'page' => $dataMapper->map('UBOS\Puck\Domain\Model\Page\PersonPage', [$this->row])[0],
                'row' => $this->row,
            ])->render();
        }
        return '';
    }
}