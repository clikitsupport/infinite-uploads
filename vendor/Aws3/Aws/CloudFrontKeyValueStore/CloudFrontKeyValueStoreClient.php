<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudFrontKeyValueStore;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudFront KeyValueStore** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeKeyValueStore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeKeyValueStoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listKeysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateKeysAsync(array $args = [])
 */
class CloudFrontKeyValueStoreClient extends AwsClient {}
