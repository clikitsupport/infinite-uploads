<?php

namespace ClikIT\Infinite_Uploads\Psr\Http\Client;

use ClikIT\Infinite_Uploads\Psr\Http\Message\RequestInterface;
use ClikIT\Infinite_Uploads\Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \ClikIT\Infinite_Uploads\Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
