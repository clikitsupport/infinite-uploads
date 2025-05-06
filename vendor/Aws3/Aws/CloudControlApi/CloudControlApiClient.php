<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudControlApi;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Cloud Control API** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelResourceRequest(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelResourceRequestAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourceRequestStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourceRequestStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceRequests(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceRequestsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResourceAsync(array $args = [])
 */
class CloudControlApiClient extends AwsClient {}
