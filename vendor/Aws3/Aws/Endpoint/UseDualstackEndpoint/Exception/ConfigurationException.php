<?php

namespace ClikIT\Infinite_Uploads\Aws\Endpoint\UseDualstackEndpoint\Exception;

use ClikIT\Infinite_Uploads\Aws\HasMonitoringEventsTrait;
use ClikIT\Infinite_Uploads\Aws\MonitoringEventsInterface;
/**
 * Represents an error interacting with configuration for useDualstackRegion
 */
class ConfigurationException extends \RuntimeException implements MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
