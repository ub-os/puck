<?php

namespace UBOS\Puck\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\LinkHandling\LinkService;

class LinkDetailsViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('parameter', 'string', 'stdWrap.typolink style parameter string', true);
        $this->registerArgument('returnAutoRel', 'boolean', 'Return automatically generated rel attribute', false, false);

    }
    public function render(): string
    {
        $parameter = $this->arguments['parameter'];
        $returnAutoRel = $this->arguments['returnAutoRel'];
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