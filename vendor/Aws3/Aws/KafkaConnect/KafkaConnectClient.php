<?php
namespace ClikIT\Infinite_Uploads\Aws\KafkaConnect;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Managed Streaming for Kafka Connect** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCustomPlugin(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCustomPluginAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createWorkerConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createWorkerConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCustomPlugin(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCustomPluginAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteWorkerConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteWorkerConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeConnectorOperation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeConnectorOperationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCustomPlugin(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCustomPluginAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeWorkerConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeWorkerConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConnectorOperations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConnectorOperationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConnectors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConnectorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCustomPlugins(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCustomPluginsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listWorkerConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listWorkerConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConnectorAsync(array $args = [])
 */
class KafkaConnectClient extends AwsClient {}
