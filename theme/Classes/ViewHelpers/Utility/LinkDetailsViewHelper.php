<?php

namespace UBOS\Theme\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\LinkHandling\LinkService;

class LinkDetailsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('parameter', 'string', 'stdWrap.typolink style parameter string', true);
        $this->registerArgument('returnAutoRel', 'boolean', 'Return automatically generated rel attribute', false, false);

    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string Linktype (page, file, url, email, folder, unknown)
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $parameter = $arguments['parameter'];
        $returnAutoRel = $arguments['returnAutoRel'];
        // workaround if parameter has _blank or other additional params
        $arr = explode(' ',trim($parameter));
        $firstparameter = $arr[0];
        $linkservice = new LinkService;
        $linkDetails = $linkservice->resolve($parameter);
        if ($returnAutoRel) {
            if ($linkDetails['type'] == 'url') {
                return 'noopener nofollow';
            } else {
                return '';
            }
        } else {
            return $linkDetails;
        }
    }
}