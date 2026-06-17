<?php

namespace UBOS\Puck\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Middleware to serve the favicon.ico file.
 *
 * This middleware checks if the request is for /favicon.ico and serves the appropriate favicon file
 * based on the site's settings.
 */
class Favicon implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if ($request->getUri()->getPath() !== '/favicon.ico') {
			return $handler->handle($request);
		}
		$faviconPackageName = $request
			->getAttribute('site')
			->getSettings()
			->get('template.favicon')
			?? 'default';
		$faviconFilePath = GeneralUtility::getFileAbsFileName(
			'EXT:puck/Resources/Public/Faviconspackages/'
			. $faviconPackageName
			. '/favicon.ico'
		);
		return new HtmlResponse(file_get_contents($faviconFilePath), 200, ['Content-Type' => 'image/x-icon']);
	}
}