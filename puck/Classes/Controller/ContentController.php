<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puckloader\Attribute\Plugin;

class ContentController extends ActionController
{
    use ContentControllerTrait;
    #[Plugin("Content")]
    public function indexAction(): ResponseInterface
    {
        $variables = $this->prepareVariables();
        $context = $this->view->getRenderingContext();
        $context->setControllerAction($this->settings['templateName']);
        $this->view->setRenderingContext($context);
        $this->view->setTemplateRootPaths(['EXT:puck/Resources/Private/Fluid/Content/']);
        $this->view->assignMultiple($variables);
        return $this->htmlResponse();
    }
}
