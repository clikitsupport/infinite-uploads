<?php

declare (strict_types=1);
namespace ClikIT\Infinite_Uploads\GuzzleHttp\Promise;

/**
 * Exception that is set as the reason for a promise that has been cancelled.
 */
class CancellationException extends RejectionException
{
}
