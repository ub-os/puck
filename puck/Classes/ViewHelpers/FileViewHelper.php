<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class FileViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('get', 'mixed', '', false, null);
        $this->registerArgument('getOnlineMediaImageSrc', 'mixed', '', false, null);
        $this->registerArgument('set', 'string', '', false, '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): mixed
    {
        $result = null;
        if ($arguments['get']) {
            $result = self::getFile($arguments['get']);
        }
        if ($arguments['getOnlineMediaImageSrc']) {
            $file = self::getFile($arguments['getOnlineMediaImageSrc']);
            if (is_object($file)) {
                return self::getOnlineMediaImageSrc($file);
            }
        }
        if ($arguments['set']) {
            $renderingContext->getVariableProvider()->add($arguments['set'], $result);
            return null;
        }
        return $result;
    }

    public static function getFile($file) {
        if (!$file) {
            return $file;
        }
        if (is_int($file)) {
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            try {
                return $resourceFactory->getFileObject($file);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        if (is_string($file)) {
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            return $resourceFactory->getFileObjectFromCombinedIdentifier($file);
        }
        if (is_object($file)) {
            if (get_class($file) === 'SMS\FluidComponents\Domain\Model\FalFile') {
                $file = $file->getFile();
            }
            if (get_class($file) === 'TYPO3\CMS\Extbase\Domain\Model\FileReference') {
                $file = $file->getOriginalResource();
            }
            if (get_class($file) === 'TYPO3\CMS\Core\Resource\FileReference') {
                return $file;
            }
            if (get_class($file) === 'TYPO3\CMS\Core\Resource\File') {
                return $file;
            }
            return 'Provided "get" argument is not a FileReference object';
        }
        return '"get" argument must be a string (file combined identifier), integer (uid) or a File/FileReference object';
    }

    protected static function getOnlineMediaImageSrc($file) {

        if ($file->getProperty('extension') === 'youtube') {
            $resolutions = array('maxresdefault', 'hqdefault', 'mqdefault');
            foreach($resolutions as $res) {
                $imgUrl = 'https://i.ytimg.com/vi/' . $file->getContents() . $res . '.jpg';
                if(@getimagesize(($imgUrl)))
                    return $imgUrl;
            }
        }
        if ($file->getProperty('extension') === 'vimeo') {
            $data = file_get_contents('https://vimeo.com/api/v2/video/' . $file->getContents() . '.json');
            $data = json_decode($data);
            return $data[0]->{'thumbnail_large'};
        }
        return null;
    }
}
