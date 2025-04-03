<?php

namespace UBOS\Puck\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use UBOS\Puck\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\RedirectResponse;

/**
 * Middleware to enable redirection for specific doktypes, similar to the core doktype 'shortcut'.
 *
 * If the doktype of the requested page is in the list of REDIRECT_DOKTYPES
 * and the 'url' field is set, redirect to the URL.
 */
class RedirectDoktypes implements MiddlewareInterface
{
	const REDIRECT_DOKTYPES = [
		PageRepository::DOKTYPES['news'],
		PageRepository::DOKTYPES['link'],
	];

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$pageRow = $request->getAttribute('frontend.page.information')->getPageRecord();
		$doktype = $pageRow['doktype'];
		$url = $pageRow['url'];
		if (!$url || !in_array($doktype, self::REDIRECT_DOKTYPES)) {
			return $handler->handle($request);
		}
		// if url is set and doktype is in REDIRECT_DOKTYPES, redirect to url
		$urlParts = parse_url($url);
		$controller = $request->getAttribute('frontend.controller');
		$cObj = GeneralUtility::makeInstance(
			ContentObjectRenderer::class,
			$controller
		);
		$url = $cObj->typoLink_URL(['parameter' => $url, 'forceAbsoluteUrl' => true]);
		$statusCode = str_starts_with(($urlParts['scheme'] ?? ''), 'http') ? 303 : 307;
		return new RedirectResponse($url, $statusCode);
	}
}