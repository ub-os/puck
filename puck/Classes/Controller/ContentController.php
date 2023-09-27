<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puckloader\Attribute\Plugin;

/**
 * Content Controller.
 */
class ContentController extends ActionController
{
/*    #[Plugin("PageMenu")]
    public function indexAction(): string
    {
        try {
            $extensionKey = $this->settings['extensionKey'];
            $vendorName = $this->settings['vendorName'];
            $name = $this->settings['contentElement'];
            $data = $this->configurationManager->getContentObject()->data;
            $targetObject = ClassNamingUtility::getFqnByPath($vendorName, $extensionKey, ($this->settings['classPath'] ?? 'Domain/Model/Content/') . $name);
            $model = ModelUtility::getModel($targetObject, $data);

            $variables = [];

            if (array_key_exists('dataProcessing', $this->settings)) {
                $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
                $dataProcessingAsTypoScriptArray = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class)->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
                $variables = $contentDataProcessor->process(
                    $this->configurationManager->getContentObject(),
                    ['dataProcessing.' => $dataProcessingAsTypoScriptArray ?? null],
                    ['data' => $data]
                );
            }

            $view = ExtendedUtility::create(StandaloneView::class);
            $context = $view->getRenderingContext();
            $context->setControllerName('Content');
            $context->setControllerAction($this->settings['contentElement']);
            $view->setRenderingContext($context);

            $variables['settings'] = $this->settings;
            $variables['object'] = $model;

            $view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);

            $view->assignMultiple(
                $variables
            );

            return $view->render();
        } catch (\Exception $ex) {
            return 'Exception in content rendering: ' . $ex->getMessage();
        }
    }*/
}
