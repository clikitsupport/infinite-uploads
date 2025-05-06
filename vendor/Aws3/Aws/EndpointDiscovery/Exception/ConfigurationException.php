<?php
namespace ClikIT\Infinite_Uploads\Aws\EndpointDiscovery\Exception;

use ClikIT\Infinite_Uploads\Aws\HasMonitoringEventsTrait;
use ClikIT\Infinite_Uploads\Aws\MonitoringEventsInterface;

/**
 * Represents an error interacting with configuration for endpoint discovery
 */
class ConfigurationException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
