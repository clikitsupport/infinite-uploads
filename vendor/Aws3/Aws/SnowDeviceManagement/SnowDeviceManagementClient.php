<?php
namespace ClikIT\Infinite_Uploads\Aws\SnowDeviceManagement;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Snow Device Management** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDeviceEc2Instances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDeviceEc2InstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeExecution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeExecutionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDeviceResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDeviceResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDevices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDevicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listExecutions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listExecutionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTasks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTasksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SnowDeviceManagementClient extends AwsClient {}
