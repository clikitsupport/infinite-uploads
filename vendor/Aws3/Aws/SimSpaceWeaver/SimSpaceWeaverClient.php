<?php
namespace ClikIT\Infinite_Uploads\Aws\SimSpaceWeaver;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS SimSpace Weaver** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSimulation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSimulationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSimulation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSimulationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listApps(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAppsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSimulations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSimulationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startClock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startClockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startSimulation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startSimulationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopClock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopClockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopSimulation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopSimulationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SimSpaceWeaverClient extends AwsClient {}
