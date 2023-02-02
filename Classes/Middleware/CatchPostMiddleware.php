<?php

namespace Cleantalk\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CatchPostMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->needProcess($request)) {
            dd($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']);
        }

        return $handler->handle($request);
    }

    private function needProcess(ServerRequestInterface $request): bool
    {
        if (
            $request->getMethod() === 'POST' &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']['enablePlugin'] &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']['accessKey']
        ) {
            return true;
        }

        return false;
    }
}