<?php
namespace ClikIT\Infinite_Uploads\Aws\SSMQuickSetup;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Systems Manager QuickSetup** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConfigurationManager(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConfigurationManagerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConfigurationManager(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConfigurationManagerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConfigurationManager(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConfigurationManagerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConfigurationManagers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConfigurationManagersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listQuickSetupTypes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listQuickSetupTypesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationManager(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationManagerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServiceSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServiceSettingsAsync(array $args = [])
 */
class SSMQuickSetupClient extends AwsClient {}
