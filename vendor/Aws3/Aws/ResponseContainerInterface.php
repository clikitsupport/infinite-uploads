<?php

namespace ClikIT\Infinite_Uploads\Aws;

use ClikIT\Infinite_Uploads\Psr\Http\Message\ResponseInterface;
interface ResponseContainerInterface
{
    /**
     * Get the received HTTP response if any.
     *
     * @return ResponseInterface|null
     */
    public function getResponse();
}
