<?php

namespace UBOS\Puck\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GetOnlineMediaPosterViewHelper extends AbstractViewHelper
{
    const ALLOWED_SERVICES = ['youtube', 'vimeo'];

    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('id', 'string', '');
        $this->registerArgument('service', 'string', '');
        $this->registerArgument('vimeoImageSize', 'string', '', false, 'large');
        $this->registerArgument('youtubeImageSize', 'string', '', false, 'maxresdefault');
    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $service = $arguments['service'];
        $id = $arguments['id'];
        $vimeoImageSize = $arguments['vimeoImageSize'];
        $youtubeImageSize = $arguments['youtubeImageSize'];
        if (!in_array($service, self::ALLOWED_SERVICES)) {
            return '';
        }
        if ($service === 'youtube') {
            $resolutions = array('maxresdefault', 'hqdefault', 'mqdefault');
            foreach($resolutions as $res) {
                $imgUrl = "https://i.ytimg.com/vi/$id/$res.jpg";
                if(@getimagesize(($imgUrl)))
                    return $imgUrl;
            }
        }
        if ($service === 'vimeo') {
            $data = file_get_contents("http://vimeo.com/api/v2/video/$id.json");
            $data = json_decode($data);
            return $data[0]->{'thumbnail_'.$vimeoImageSize};
        }
    }
}
