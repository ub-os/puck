<?php
namespace UBOS\Puck\ViewHelpers\Repository\Page;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use UBOS\Puck\Domain\Repository\PageRepository;

class GetPagesAndSubPagesViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('pages', 'string', '');
        $this->registerArgument('parents', 'string', '');

    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pageRepository = $objectManager->get(PageRepository::class);
        return $pageRepository->getPagesAndSubPages($arguments['pages'], $arguments['parents']);
    }
}
