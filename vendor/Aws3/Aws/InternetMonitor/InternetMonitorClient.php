<?php
namespace ClikIT\Infinite_Uploads\Aws\InternetMonitor;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudWatch Internet Monitor** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getHealthEvent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getHealthEventAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInternetEvent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInternetEventAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMonitorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getQueryResults(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getQueryResultsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getQueryStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getQueryStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listHealthEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listHealthEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInternetEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInternetEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMonitors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMonitorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateMonitor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateMonitorAsync(array $args = [])
 */
class InternetMonitorClient extends AwsClient {}
