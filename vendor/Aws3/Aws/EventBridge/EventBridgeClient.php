<?php
namespace ClikIT\Infinite_Uploads\Aws\EventBridge;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon EventBridge** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result activateEventSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise activateEventSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelReplay(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelReplayAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createApiDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createApiDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createArchive(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createArchiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConnection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConnectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEventBus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEventBusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPartnerEventSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPartnerEventSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deactivateEventSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deactivateEventSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deauthorizeConnection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deauthorizeConnectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApiDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApiDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteArchive(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteArchiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConnection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConnectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEventBus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEventBusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePartnerEventSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePartnerEventSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeApiDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeApiDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeArchive(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeArchiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeConnection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeConnectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventBus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventBusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describePartnerEventSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describePartnerEventSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeReplay(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeReplayAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listApiDestinations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listApiDestinationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listArchives(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listArchivesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConnections(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConnectionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEndpointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEventBuses(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEventBusesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEventSources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEventSourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPartnerEventSourceAccounts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPartnerEventSourceAccountsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPartnerEventSources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPartnerEventSourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listReplays(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listReplaysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRuleNamesByTarget(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRuleNamesByTargetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRules(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRulesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTargetsByRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTargetsByRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putPartnerEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putPartnerEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putPermission(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putPermissionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putTargets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putTargetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removePermission(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removePermissionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeTargets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeTargetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startReplay(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startReplayAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testEventPattern(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testEventPatternAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateApiDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateApiDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateArchive(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateArchiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConnection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConnectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateEventBus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateEventBusAsync(array $args = [])
 */
class EventBridgeClient extends AwsClient {
    public function __construct(array $args)
    {
        parent::__construct($args);

        if ($this->isUseEndpointV2()) {
            $stack = $this->getHandlerList();
            $isCustomEndpoint = isset($args['endpoint']);
            $stack->appendBuild(
                EventBridgeEndpointMiddleware::wrap(
                    $this->getRegion(),
                    [
                        'use_fips_endpoint' =>
                            $this->getConfig('use_fips_endpoint')->isUseFipsEndpoint(),
                        'dual_stack' =>
                            $this->getConfig('use_dual_stack_endpoint')->isUseDualStackEndpoint(),
                    ],
                    $this->getConfig('endpoint_provider'),
                    $isCustomEndpoint
                ),
                'eventbridge.endpoint_middleware'
            );
        }
    }
}
