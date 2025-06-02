<?php
namespace ClikIT\Infinite_Uploads\Aws\RedshiftDataAPIService;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Redshift Data API Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchExecuteStatement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelStatement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelStatementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStatement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStatementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeStatement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStatementResult(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStatementResultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStatementResultV2(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStatementResultV2Async(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDatabases(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDatabasesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSchemas(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSchemasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStatements(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStatementsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTables(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 */
class RedshiftDataAPIServiceClient extends AwsClient {}
