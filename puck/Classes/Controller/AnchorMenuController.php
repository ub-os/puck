<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puckloader\Attribute\Plugin;

class AnchorMenuController extends ActionController
{
    use ContentControllerDataProcessingTrait;
    #[Plugin("AnchorMenu")]
    public function anchorMenuAction(): ResponseInterface
    {
        $langId = (int)$this->request->getAttribute('language')->getLanguageId();
        $variables = $this->prepareVariables();
        $variables['settings'] = $this->settings;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $variables['menu'] = $queryBuilder
            ->select('*')->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $variables['record']->getPid()),
                $queryBuilder->expr()->eq('CType',$queryBuilder->createNamedParameter('puck_anchor')),
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('sys_language_uid', $langId)
            )
            ->executeQuery()->fetchAllAssociative();
        $this->setContentTemplatePath();
        $this->view->assignMultiple($variables);
        return $this->htmlResponse();
    }

}
