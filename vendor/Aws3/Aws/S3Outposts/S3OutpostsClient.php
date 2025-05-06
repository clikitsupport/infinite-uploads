<?php
namespace ClikIT\Infinite_Uploads\Aws\S3Outposts;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon S3 on Outposts** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEndpointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOutpostsWithS3(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOutpostsWithS3Async(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSharedEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSharedEndpointsAsync(array $args = [])
 */
class S3OutpostsClient extends AwsClient {}
