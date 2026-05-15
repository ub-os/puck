<?php

namespace UBOS\Puck\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Http\RedirectResponse;
use UBOS\Puck\Constants;

/**
 * Middleware to enable redirection for specific doktypes, similar to the core doktype 'shortcut'.
 *
 * If the doktype of the requested page is in the list of $redirectDoktypes
 * and the 'url' field is set, redirect to the URL.
 */
class RedirectDoktypes implements MiddlewareInterface
{
	protected array $redirectDoktypes = [
		Constants::DOKTYPES['news'],
		Constants::DOKTYPES['link'],
	];

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$pageRow = $request->getAttribute('frontend.page.information')->getPageRecord();
		$doktype = $pageRow['doktype'];
		$url = $pageRow['url'];
		if (!$url || !in_array($doktype, $this->redirectDoktypes)) {
			return $handler->handle($request);
		}
		// if url is set and doktype is in $redirectDoktypes, redirect to url
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