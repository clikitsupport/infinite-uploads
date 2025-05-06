<?php
namespace ClikIT\Infinite_Uploads\Aws\RDSDataService;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS RDS DataService** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchExecuteStatement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result beginTransaction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise beginTransactionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result commitTransaction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise commitTransactionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeSql(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeSqlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeStatement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result rollbackTransaction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise rollbackTransactionAsync(array $args = [])
 */
class RDSDataServiceClient extends AwsClient {}
