<?php

namespace Cleantalk\Classes\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class handleGeneralPostData implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cleantalk_integrations = new cleantalkIntegrations();
        $integration_data = $cleantalk_integrations->getIntegrationData($request);

        if ( $integration_data ) {
            $cleantalk_integrations->integrationSpamCheck($integration_data);
        }

        return $this->requestAfterAdditionalActions($request, $handler, $integration_data, true);
    }

    private function requestAfterAdditionalActions(ServerRequestInterface $request, RequestHandlerInterface $handler, $integration_data, $do_default_actions = false)
    {
//        if (!$do_default_actions) {
//            if ($integration_data['integration_name'] === 'something') {
//                //do something before further request handled
//            }
//        }

        return $handler->handle($request);
    }
}
