<?php

namespace UBOS\Puck\Hooks\WebLayoutFooter;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Information\Typo3Version;


class IncludeJavascript
{
    public function loadModules(): void
    {
       $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() > 11) {
            $pageRenderer->loadJavaScriptModule(
                '@ubos/puck/web-layout-remember-scroll-pos.js'
            );
        } else {
            // keep RequireJs for TYPO3 below v12.0
            $pageRenderer->loadRequireJsModule(
                'TYPO3/CMS/Puck/web-layout-remember-scroll-pos'
            );
        }
    }
}