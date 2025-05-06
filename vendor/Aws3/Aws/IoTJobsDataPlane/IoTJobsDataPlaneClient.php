<?php
namespace ClikIT\Infinite_Uploads\Aws\IoTJobsDataPlane;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Jobs Data Plane** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeJobExecution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeJobExecutionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPendingJobExecutions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPendingJobExecutionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startCommandExecution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startCommandExecutionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startNextPendingJobExecution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startNextPendingJobExecutionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateJobExecution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateJobExecutionAsync(array $args = [])
 */
class IoTJobsDataPlaneClient extends AwsClient {}
