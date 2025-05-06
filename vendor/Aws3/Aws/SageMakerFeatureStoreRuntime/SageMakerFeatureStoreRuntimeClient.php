<?php
namespace ClikIT\Infinite_Uploads\Aws\SageMakerFeatureStoreRuntime;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon SageMaker Feature Store Runtime** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchGetRecord(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchGetRecordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRecord(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRecordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRecord(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRecordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRecord(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRecordAsync(array $args = [])
 */
class SageMakerFeatureStoreRuntimeClient extends AwsClient {}
