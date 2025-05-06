<?php
namespace ClikIT\Infinite_Uploads\Aws\Braket;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Braket** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelQuantumTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelQuantumTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createQuantumTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createQuantumTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getQuantumTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getQuantumTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchDevices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchDevicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchQuantumTasks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchQuantumTasksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class BraketClient extends AwsClient {}
