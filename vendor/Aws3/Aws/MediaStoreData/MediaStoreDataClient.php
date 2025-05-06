<?php
namespace ClikIT\Infinite_Uploads\Aws\MediaStoreData;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elemental MediaStore Data Plane** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteObject(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteObjectAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeObject(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeObjectAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getObject(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getObjectAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listItems(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listItemsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putObject(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putObjectAsync(array $args = [])
 */
class MediaStoreDataClient extends AwsClient {}
