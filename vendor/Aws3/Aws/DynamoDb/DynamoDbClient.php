<?php
namespace ClikIT\Infinite_Uploads\Aws\DynamoDb;

use ClikIT\Infinite_Uploads\Aws\Api\Parser\Crc32ValidatingParser;
use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\ClientResolver;
use ClikIT\Infinite_Uploads\Aws\Exception\AwsException;
use ClikIT\Infinite_Uploads\Aws\HandlerList;
use ClikIT\Infinite_Uploads\Aws\Middleware;
use ClikIT\Infinite_Uploads\Aws\RetryMiddleware;
use ClikIT\Infinite_Uploads\Aws\RetryMiddlewareV2;

/**
 * This client is used to interact with the **Amazon DynamoDB** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchGetItem(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchGetItemAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchWriteItem(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchWriteItemAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteItem(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteItemAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getItem(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getItemAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTables(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putItem(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putItemAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result query(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise queryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result scan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise scanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateItem(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateItemAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchExecuteStatement(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGlobalTable(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGlobalTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResourcePolicy(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourcePolicyAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeContinuousBackups(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeContinuousBackupsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeContributorInsights(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeContributorInsightsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEndpoints(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEndpointsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeExport(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeExportAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeGlobalTable(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeGlobalTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeGlobalTableSettings(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeGlobalTableSettingsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeImport(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeImportAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLimits(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLimitsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTableReplicaAutoScaling(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTableReplicaAutoScalingAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTimeToLive(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTimeToLiveAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeStatement(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeTransaction(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeTransactionAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportTableToPointInTime(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportTableToPointInTimeAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourcePolicy(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result importTable(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listBackups(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listBackupsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listContributorInsights(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listContributorInsightsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listExports(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listExportsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGlobalTables(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGlobalTablesAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listImports(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listImportsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsOfResource(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsOfResourceAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResourcePolicy(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResourcePolicyAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreTableFromBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreTableFromBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreTableToPointInTime(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreTableToPointInTimeAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result transactGetItems(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise transactGetItemsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result transactWriteItems(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise transactWriteItemsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateContinuousBackups(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateContinuousBackupsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateContributorInsights(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateContributorInsightsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGlobalTable(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGlobalTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGlobalTableSettings(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGlobalTableSettingsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTableReplicaAutoScaling(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTableReplicaAutoScalingAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTimeToLive(array $args = []) (supported in versions 2012-08-10)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTimeToLiveAsync(array $args = []) (supported in versions 2012-08-10)
 */
class DynamoDbClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 10;
        $args['retries']['fn'] = [__CLASS__, '_applyRetryConfig'];
        $args['api_provider']['fn'] = [__CLASS__, '_applyApiProvider'];

        return $args;
    }

    /**
     * Convenience method for instantiating and registering the DynamoDB
     * Session handler with this DynamoDB client object.
     *
     * @param array $config Array of options for the session handler factory
     *
     * @return SessionHandler
     */
    public function registerSessionHandler(array $config = [])
    {
        $handler = SessionHandler::fromClient($this, $config);
        $handler->register();

        return $handler;
    }

    /** @internal */
    public static function _applyRetryConfig($value, array &$args, HandlerList $list)
    {
        if ($value) {
            $config = \ClikIT\Infinite_Uploads\Aws\Retry\ConfigurationProvider::unwrap($value);

            if ($config->getMode() === 'legacy') {
                $list->appendSign(
                    Middleware::retry(
                        RetryMiddleware::createDefaultDecider(
                            $config->getMaxAttempts() - 1,
                            ['error_codes' => ['TransactionInProgressException']]
                        ),
                        function ($retries) {
                            return $retries
                                ? RetryMiddleware::exponentialDelay($retries) / 2
                                : 0;
                        },
                        isset($args['stats']['retries'])
                            ? (bool)$args['stats']['retries']
                            : false
                    ),
                    'retry'
                );
            } else {
                $list->appendSign(
                    RetryMiddlewareV2::wrap(
                        $config,
                        [
                            'collect_stats' => $args['stats']['retries'],
                            'transient_error_codes' => ['TransactionInProgressException']
                        ]
                    ),
                    'retry'
                );
            }
        }
    }

    /** @internal */
    public static function _applyApiProvider($value, array &$args, HandlerList $list)
    {
        ClientResolver::_apply_api_provider($value, $args);
        $args['parser'] = new Crc32ValidatingParser($args['parser']);
    }
}
