<?php

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverDelegateRegistry;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Controller trait for content element plugins rendering Fluid components,
 * specifically for CTypes configured with @see \UBOS\Puck\Configuration\ContentElementConfiguration
 *
 * Provides methods to process content element variables and directly render Fluid components.
 */
trait ComponentContentElementTrait
{
    protected RequestInterface $request;
    protected ViewInterface $view;
    protected array $settings;

    private ContentDataProcessor $contentDataProcessor;
    private ViewHelperResolverDelegateRegistry $delegateRegistry;

    public function injectContentDataProcessor(ContentDataProcessor $contentDataProcessor): void
    {
        $this->contentDataProcessor = $contentDataProcessor;
    }

    public function injectViewHelperResolverDelegateRegistry(ViewHelperResolverDelegateRegistry $delegateRegistry): void
    {
        $this->delegateRegistry = $delegateRegistry;
    }

    /**
     * Runs data processing as configured in settings[contentElementConfiguration][dataProcessing]
     * and returns the resulting variables.
     *
     * Usage in controller:
     *   $variables = $this->getProcessedData();
     *   return $this->htmlResponse($this->renderComponent($variables));
     */
    protected function getProcessedData(): array
    {
        $cObj = $this->request->getAttribute('currentContentObject');
        $variables = ['data' => $cObj->data];

        $contentElementConfiguration = $this->settings['contentElementConfiguration'] ?? [];
        if ($contentElementConfiguration['dataProcessing'] ?? false) {
            $processingTypoScript = $this->convertToTypoScriptArray($contentElementConfiguration['dataProcessing']);
            $variables = $this->contentDataProcessor->process(
                $cObj,
                ['dataProcessing.' => $processingTypoScript],
                $variables,
            );
        }

        return $variables;
    }

    /**
     * Directly renders a Fluid component.
	 * @param array 	  $arguments 			Variables to pass
	 * @param string|null $component            Component name, defaults to settings[contentElementConfiguration][component]
     *                                          or the current controller action name
     * @param string|null $componentCollection  PHP namespace of the component collection, defaults to
     *                                          settings[contentElementConfiguration][componentCollection]
     */
    protected function renderComponent(
		array 	$arguments,
		?string $component = null,
        ?string $componentCollection = null,
    ): string {
        $componentCollection = $componentCollection
            ?? $this->settings['contentElementConfiguration']['componentCollection']
            ?? throw new \RuntimeException(
                'No component collection configured in settings[contentElementConfiguration][componentCollection]',
                1676412345,
            );

        $component = $component
            ?? $this->settings['contentElementConfiguration']['component']
            ?? lcfirst($this->resolveRenderingContext()->getControllerAction());


        $delegate = $this->delegateRegistry->getAll()[$componentCollection] ?? null;
        if (!$delegate instanceof ComponentDefinitionProviderInterface) {
            throw new \RuntimeException(
                sprintf('No component collection found for namespace "%s"', $componentCollection),
                1676412346,
            );
        }

        return $delegate->getComponentRenderer()->renderComponent(
            $component,
			$arguments,
            [],
            $this->resolveRenderingContext(),
        );
    }

    private function resolveRenderingContext(): RenderingContextInterface
    {
        if (!$this->view instanceof FluidViewAdapter) {
            throw new \RuntimeException(
                'Rendering Fluid components requires a Fluid view.',
                1676412347,
            );
        }
        return $this->view->getRenderingContext();
    }

    /**
     * Converts a plain nested array (as stored in Extbase settings) to a TypoScript array
     * with dot-suffixed keys for sub-arrays.
     */
    private function convertToTypoScriptArray(array $plainArray): array
    {
        $typoScriptArray = [];
        foreach ($plainArray as $key => $value) {
            if (is_array($value)) {
                $typoScriptArray[$key . '.'] = $this->convertToTypoScriptArray($value);
            } else {
                $typoScriptArray[$key] = $value;
            }
        }
        return $typoScriptArray;
    }
}
