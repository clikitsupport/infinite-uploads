<?php
namespace ClikIT\Infinite_Uploads\Aws\Firehose;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Firehose** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDeliveryStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDeliveryStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDeliveryStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDeliveryStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDeliveryStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDeliveryStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDeliveryStreams(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDeliveryStreamsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForDeliveryStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForDeliveryStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRecord(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRecordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRecordBatch(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRecordBatchAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDeliveryStreamEncryption(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDeliveryStreamEncryptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopDeliveryStreamEncryption(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopDeliveryStreamEncryptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagDeliveryStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagDeliveryStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagDeliveryStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagDeliveryStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDestinationAsync(array $args = [])
 */
class FirehoseClient extends AwsClient {}
