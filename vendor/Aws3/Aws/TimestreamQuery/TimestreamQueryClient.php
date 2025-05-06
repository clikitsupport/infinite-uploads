<?php
namespace ClikIT\Infinite_Uploads\Aws\TimestreamQuery;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Timestream Query** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createScheduledQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createScheduledQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteScheduledQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteScheduledQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEndpointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeScheduledQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeScheduledQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeScheduledQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeScheduledQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listScheduledQueries(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listScheduledQueriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result prepareQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise prepareQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result query(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise queryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAccountSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAccountSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateScheduledQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateScheduledQueryAsync(array $args = [])
 */
class TimestreamQueryClient extends AwsClient {}
