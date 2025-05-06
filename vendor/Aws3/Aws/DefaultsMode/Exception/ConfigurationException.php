<?php
namespace ClikIT\Infinite_Uploads\Aws\DefaultsMode\Exception;

use ClikIT\Infinite_Uploads\Aws\HasMonitoringEventsTrait;
use ClikIT\Infinite_Uploads\Aws\MonitoringEventsInterface;

/**
 * Represents an error interacting with configuration mode
 */
class ConfigurationException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
