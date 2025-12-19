<?php

namespace ClikIT\Infinite_Uploads\GuzzleHttp;

use ClikIT\Infinite_Uploads\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}
