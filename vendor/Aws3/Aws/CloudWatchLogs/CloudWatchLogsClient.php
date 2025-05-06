<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudWatchLogs;

use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use Generator;

/**
 * This client is used to interact with the **Amazon CloudWatch Logs** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateKmsKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateKmsKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelExportTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelExportTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDelivery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDeliveryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createExportTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createExportTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLogAnomalyDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLogAnomalyDetectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLogGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLogGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLogStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLogStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccountPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccountPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDataProtectionPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDataProtectionPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDelivery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDeliveryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDeliveryDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDeliveryDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDeliveryDestinationPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDeliveryDestinationPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDeliverySource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDeliverySourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteIndexPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteIndexPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLogAnomalyDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLogAnomalyDetectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLogGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLogGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLogStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLogStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMetricFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMetricFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteQueryDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteQueryDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRetentionPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRetentionPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSubscriptionFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSubscriptionFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTransformer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTransformerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeConfigurationTemplates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeConfigurationTemplatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDeliveries(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDeliveriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDeliveryDestinations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDeliveryDestinationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDeliverySources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDeliverySourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDestinations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDestinationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeExportTasks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeExportTasksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeFieldIndexes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeFieldIndexesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeIndexPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeIndexPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLogGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLogGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLogStreams(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLogStreamsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeMetricFilters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeMetricFiltersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeQueries(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeQueriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeQueryDefinitions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeQueryDefinitionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeResourcePolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeResourcePoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSubscriptionFilters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSubscriptionFiltersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateKmsKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateKmsKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result filterLogEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise filterLogEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDataProtectionPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDataProtectionPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDelivery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeliveryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDeliveryDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeliveryDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDeliveryDestinationPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeliveryDestinationPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDeliverySource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeliverySourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLogAnomalyDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLogAnomalyDetectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLogEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLogEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLogGroupFields(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLogGroupFieldsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLogRecord(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLogRecordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getQueryResults(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getQueryResultsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTransformer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTransformerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAnomalies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAnomaliesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listIntegrations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listIntegrationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLogAnomalyDetectors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLogAnomalyDetectorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLogGroupsForQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLogGroupsForQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsLogGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsLogGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccountPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccountPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putDataProtectionPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putDataProtectionPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putDeliveryDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putDeliveryDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putDeliveryDestinationPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putDeliveryDestinationPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putDeliverySource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putDeliverySourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putDestinationPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putDestinationPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putIndexPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putIndexPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putLogEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putLogEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putMetricFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putMetricFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putQueryDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putQueryDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRetentionPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRetentionPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putSubscriptionFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putSubscriptionFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putTransformer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putTransformerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startLiveTail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startLiveTailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopQuery(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopQueryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagLogGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagLogGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testMetricFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testMetricFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testTransformer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testTransformerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagLogGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagLogGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAnomaly(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAnomalyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDeliveryConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDeliveryConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLogAnomalyDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLogAnomalyDetectorAsync(array $args = [])
 */
class CloudWatchLogsClient extends AwsClient {
    static $streamingCommands = [
        'StartLiveTail' => true
    ];

    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->addStreamingFlagMiddleware();
    }

    private function addStreamingFlagMiddleware()
    {
        $this->getHandlerList()
            -> appendInit(
                $this->getStreamingFlagMiddleware(),
                'streaming-flag-middleware'
            );
    }

    private function getStreamingFlagMiddleware(): callable
    {
        return function (callable $handler) {
            return function (CommandInterface $command, $request = null) use ($handler) {
                if (!empty(self::$streamingCommands[$command->getName()])) {
                    $command['@http']['stream'] = true;
                }

                return $handler($command, $request);
            };
        };
    }

    /**
     * Helper method for 'startLiveTail' operation that checks for results.
     *
     * Initiates 'startLiveTail' operation with given arguments, and continuously
     * checks response stream for session updates or results, yielding each
     * stream chunk when results are not empty. This method abstracts from users
     * the need of checking if there are logs entry available to be watched, which means
     * that users will always get a next item to be iterated when more log entries are
     * available.
     *
     * @param array $args Command arguments.
     *
     * @return Generator Yields session update or result stream chunks.
     */
    public function startLiveTailCheckingForResults(array $args): Generator
    {
        $response = $this->startLiveTail($args);
        foreach ($response['responseStream'] as $streamChunk) {
            if (isset($streamChunk['sessionUpdate'])) {
                if (!empty($streamChunk['sessionUpdate']['sessionResults'])) {
                    yield $streamChunk;
                }
            } else {
                yield $streamChunk;
            }
        }
    }
}
