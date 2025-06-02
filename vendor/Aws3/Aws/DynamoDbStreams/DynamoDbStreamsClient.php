<?php
namespace ClikIT\Infinite_Uploads\Aws\DynamoDbStreams;

use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\DynamoDb\DynamoDbClient;

/**
 * This client is used to interact with the **Amazon DynamoDb Streams** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRecords(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getShardIterator(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStreams(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 */
class DynamoDbStreamsClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 11;
        $args['retries']['fn'] = [DynamoDbClient::class, '_applyRetryConfig'];

        return $args;
    }
}