<?php

namespace ClikIT\Infinite_Uploads\Aws\Auth\Exception;

use ClikIT\Infinite_Uploads\Aws\HasMonitoringEventsTrait;
use ClikIT\Infinite_Uploads\Aws\MonitoringEventsInterface;

/**
 * Represents an error when attempting to resolve authentication.
 */
class UnresolvedAuthSchemeException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
