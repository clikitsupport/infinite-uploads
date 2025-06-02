<?php
namespace ClikIT\Infinite_Uploads\Aws\SagemakerEdgeManager;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Sagemaker Edge Manager** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDeployments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeploymentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDeviceRegistration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeviceRegistrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendHeartbeat(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendHeartbeatAsync(array $args = [])
 */
class SagemakerEdgeManagerClient extends AwsClient {}
