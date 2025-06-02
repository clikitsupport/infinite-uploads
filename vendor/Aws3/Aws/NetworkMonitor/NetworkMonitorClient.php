<?php
namespace ClikIT\Infinite_Uploads\Aws\NetworkMonitor;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudWatch Network Monitor** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createProbe(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createProbeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteProbe(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteProbeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getProbe(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getProbeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMonitors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMonitorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateProbe(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateProbeAsync(array $args = [])
 */
class NetworkMonitorClient extends AwsClient {}
