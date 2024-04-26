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

    public function echoExceptionWeb(\Throwable $exception): void
    {
        if ($this->backendUserIsLoggedIn()) {
            $this->echoDebugException($exception);
            return;
        }

        $request = $GLOBALS['TYPO3_REQUEST'];
        $errorHandler = $this->getErrorHandlerFromSite($request, 503);
        if ($errorHandler !== null) {
            $this->echoErrorPage($exception, $errorHandler, $request);
            return;
        }

        $this->echoProductionException($exception);
    }

    protected function echoDebugException(\Throwable $exception): void
    {
        parent::echoExceptionWeb($exception);
    }

    protected function echoErrorPage(\Throwable $exception, PageErrorHandlerInterface $errorHandler, ServerRequestInterface $request): void
    {
        $this->sendStatusHeaders($exception);
        $this->writeLogEntries($exception, self::CONTEXT_WEB);
        $errorResponse = $errorHandler->handlePageError($request, 'An error occurred', ['reasons' => $exception->getMessage()]);
        echo $errorResponse->getBody();
    }

    protected function echoProductionException(\Throwable $exception): void
    {
        $productionExceptionHandler = GeneralUtility::makeInstance(ProductionExceptionHandler::class);
        $productionExceptionHandler->echoExceptionWeb($exception);
    }

    protected function backendUserIsLoggedIn(): bool
    {
        return !!$GLOBALS['BE_USER']?->user['uid'];
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