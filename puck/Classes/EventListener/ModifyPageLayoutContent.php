<?php

namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Fluid\View\StandaloneView;
use UBOS\Puck\Domain\Repository\PageRepository;

final class ModifyPageLayoutContent
{
    protected array $row = [];
    protected int $id = 0;

    #[AsEventListener]
    public function __invoke(
        ModifyPageLayoutContentEvent $event
    ): void
    {
        $this->id = $event->getRequest()->getQueryParams()['id'];
        $this->row = BackendUtility::readPageAccess($this->id, true);
        $event->addHeaderContent($this->renderPageTypeHeaders());
        $defaultFooter = $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/PageLayoutContent/Footer/Default.html', [
            'row' => $this->row,
        ])->render();
        $event->addFooterContent($defaultFooter);
    }

    protected function renderPageTypeHeaders(): string
    {
        if ((int)($this->row['doktype'] ?? 0) === PageRepository::DOKTYPES['news']) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/PageLayoutContent/Header/NewsPage.html', [
                'page' => $dataMapper->map('UBOS\Puck\Domain\Model\Page\NewsPage', [$this->row])[0],
                'row' => $this->row,
            ])->render();
        }
        if ((int)($this->row['doktype'] ?? 0) === PageRepository::DOKTYPES['person']) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/PageLayoutContent/Header/PersonPage.html', [
                'page' => $dataMapper->map('UBOS\Puck\Domain\Model\Page\PersonPage', [$this->row])[0],
                'row' => $this->row,
            ])->render();
        }
        return '';
    }

    protected function createView(string $pathAndFilename, array $variables = null): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($pathAndFilename));
        $view->assignMultiple($variables ?? []);

        return $view;
    }
}