<?php

namespace UBOS\Puck\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use UBOS\Puck\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\RedirectResponse;

class RedirectDoktypes implements MiddlewareInterface
{
    const REDIRECT_DOKTYPES = [
        PageRepository::DOKTYPES['news']
    ];

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $doktype = $GLOBALS['TSFE']->page['doktype'];
        $url = $GLOBALS['TSFE']->page['url'];
        // if url is set and doktype is in REDIRECT_DOKTYPES, redirect to url
        if ($url && in_array($doktype, self::REDIRECT_DOKTYPES)) {
            // taken from ext:doktype_typolink
            $urlParts = parse_url($url);
            if (($urlParts['scheme'] ?? false) === 't3') {
                // it's a typolink that needs to be resolved!
                // Initialize configuration
                // (stolen from PrepareTypoScriptFrontendRendering)
                $controller = $request->getAttribute('frontend.controller');
                $GLOBALS['TYPO3_REQUEST'] = $request;
                $request = $controller->getFromCache($request);
                $GLOBALS['TYPO3_REQUEST'] = $request;
                // Get instance of ContentObjectRenderer
                $cObj = GeneralUtility::makeInstance(
                    ContentObjectRenderer::class,
                    $GLOBALS['TSFE']
                );
                $url = $cObj->typoLink_URL(['parameter' => $url, 'forceAbsoluteUrl' => true]);
                return new RedirectResponse($url, 308);
            }
        }

        return $handler->handle($request);
    }
}