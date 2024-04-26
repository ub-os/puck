<?php

namespace UBOS\Puck\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Error\DebugExceptionHandler;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerNotConfiguredException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUserExceptionHandler extends DebugExceptionHandler
{

    protected function backendUserIsLoggedIn(): bool
    {
        return !!$GLOBALS['BE_USER']?->user['uid'];
    }

    public function echoExceptionWeb(\Throwable $exception): void
    {
        $this->sendStatusHeaders($exception);
        $this->writeLogEntries($exception, self::CONTEXT_WEB);

        // if backend user is logged in, show the debug exception
        if ($this->backendUserIsLoggedIn()) {
            $content = $this->getContent($exception);
            $css = $this->getStylesheet();
            echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>TYPO3 Exception</title>
        <meta name="robots" content="noindex,nofollow" />
        <style>$css</style>
    </head>
    <body>
        $content
    </body>
</html>
HTML;
            return;
        }

        // otherwise if there is a site with a configured error handler for 503, use it
        $request = $GLOBALS['TYPO3_REQUEST'];
        $errorHandler = $this->getErrorHandlerFromSite($request, 503);
        if ($errorHandler !== null) {
            $errorResponse = $errorHandler->handlePageError($request, 'An error occurred', ['reasons' => $exception->getMessage()]);
            echo $errorResponse->getBody();
            return;
        }

        // otherwise use the production exception handler
        $productionExceptionHandler = GeneralUtility::makeInstance(ProductionExceptionHandler::class);
        $productionExceptionHandler->echoExceptionWeb($exception);

    }

    protected function getErrorHandlerFromSite(ServerRequestInterface $request, int $statusCode): ?PageErrorHandlerInterface
    {
        $site = $request->getAttribute('site');
        if ($site instanceof Site) {
            try {
                return $site->getErrorHandler($statusCode);
            } catch (PageErrorHandlerNotConfiguredException $e) {
                // No error handler found, so fallback back to the generic TYPO3 error handler.
            }
        }
        return null;
    }


}