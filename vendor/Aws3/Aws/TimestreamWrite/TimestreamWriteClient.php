<?php
namespace ClikIT\Infinite_Uploads\Aws\TimestreamWrite;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Timestream Write** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createBatchLoadTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createBatchLoadTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDatabase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDatabaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDatabase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDatabaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeBatchLoadTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeBatchLoadTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDatabase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDatabaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEndpointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listBatchLoadTasks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listBatchLoadTasksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDatabases(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDatabasesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTables(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resumeBatchLoadTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resumeBatchLoadTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDatabase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDatabaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result writeRecords(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise writeRecordsAsync(array $args = [])
 */
class TimestreamWriteClient extends AwsClient {}
