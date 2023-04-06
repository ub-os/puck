<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use HDNET\Autoloader\Utility\ClassNamingUtility;
use HDNET\Autoloader\Utility\ExtendedUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use HDNET\Autoloader\Annotation\NoCache;
use HDNET\Autoloader\Annotation\Plugin;

/**
 * Content Controller.
 */
class ContentController extends ActionController
{
    /**
     * Render the content Element via ExtBase.
     * @Plugin("Content")
     */
    public function indexAction(): string
    {
        try {
            $extensionKey = $this->settings['extensionKey'];
            $vendorName = $this->settings['vendorName'];
            $name = $this->settings['contentElement'];
            $data = $this->configurationManager->getContentObject()->data;
            $targetObject = ClassNamingUtility::getFqnByPath($vendorName, $extensionKey, ($this->settings['classPath'] ?? 'Domain/Model/Content/') . $name);
            $model = ModelUtility::getModel($targetObject, $data);
            $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $dataProcessingAsTypoScriptArray = [];
            if (array_key_exists('dataProcessing', $this->settings)) {
                $dataProcessingAsTypoScriptArray = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class)->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
            }
            $variables = $contentDataProcessor->process(
                $this->configurationManager->getContentObject(),
                ['dataProcessing.' => $dataProcessingAsTypoScriptArray ?? null],
                ['data' => $data]
            );

            /** @var StandaloneView $view */
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
    }
}
