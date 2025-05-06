<?php
namespace ClikIT\Infinite_Uploads\Aws\Exception;

use ClikIT\Infinite_Uploads\Aws\HasMonitoringEventsTrait;
use ClikIT\Infinite_Uploads\Aws\MonitoringEventsInterface;

class InvalidJsonException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
