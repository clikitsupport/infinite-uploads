<?php
namespace ClikIT\Infinite_Uploads\Aws\ApplicationSignals;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudWatch Application Signals** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchGetServiceLevelObjectiveBudgetReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchGetServiceLevelObjectiveBudgetReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchUpdateExclusionWindows(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchUpdateExclusionWindowsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createServiceLevelObjective(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createServiceLevelObjectiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServiceLevelObjective(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServiceLevelObjectiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getService(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceLevelObjective(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceLevelObjectiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceDependencies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceDependenciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceDependents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceDependentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceLevelObjectiveExclusionWindows(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceLevelObjectiveExclusionWindowsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceLevelObjectives(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceLevelObjectivesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceOperations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceOperationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDiscovery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDiscoveryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServiceLevelObjective(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServiceLevelObjectiveAsync(array $args = [])
 */
class ApplicationSignalsClient extends AwsClient {}
