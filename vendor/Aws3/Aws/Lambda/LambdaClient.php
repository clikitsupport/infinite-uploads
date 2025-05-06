<?php
namespace ClikIT\Infinite_Uploads\Aws\Lambda;

use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use ClikIT\Infinite_Uploads\Aws\Middleware;

/**
 * This client is used to interact with AWS Lambda
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result addLayerVersionPermission(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addLayerVersionPermissionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addPermission(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addPermissionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEventSourceMapping(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEventSourceMappingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFunction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFunctionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFunctionUrlConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFunctionUrlConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEventSourceMapping(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEventSourceMappingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFunction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFunctionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFunctionCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFunctionCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFunctionConcurrency(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFunctionConcurrencyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFunctionEventInvokeConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFunctionEventInvokeConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFunctionUrlConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFunctionUrlConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLayerVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLayerVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteProvisionedConcurrencyConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteProvisionedConcurrencyConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEventSourceMapping(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEventSourceMappingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunctionCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunctionConcurrency(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionConcurrencyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunctionConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunctionEventInvokeConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionEventInvokeConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunctionRecursionConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionRecursionConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunctionUrlConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionUrlConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLayerVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLayerVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLayerVersionByArn(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLayerVersionByArnAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLayerVersionPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLayerVersionPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getProvisionedConcurrencyConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getProvisionedConcurrencyConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRuntimeManagementConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRuntimeManagementConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result invoke(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise invokeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result invokeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise invokeAsyncAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result invokeWithResponseStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise invokeWithResponseStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAliases(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAliasesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCodeSigningConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCodeSigningConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEventSourceMappings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEventSourceMappingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFunctionEventInvokeConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFunctionEventInvokeConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFunctionUrlConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFunctionUrlConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFunctions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFunctionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFunctionsByCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFunctionsByCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLayerVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLayerVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLayers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLayersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listProvisionedConcurrencyConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listProvisionedConcurrencyConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVersionsByFunction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVersionsByFunctionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result publishLayerVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise publishLayerVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result publishVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise publishVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putFunctionCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putFunctionCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putFunctionConcurrency(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putFunctionConcurrencyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putFunctionEventInvokeConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putFunctionEventInvokeConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putFunctionRecursionConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putFunctionRecursionConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putProvisionedConcurrencyConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putProvisionedConcurrencyConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRuntimeManagementConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRuntimeManagementConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeLayerVersionPermission(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeLayerVersionPermissionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removePermission(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removePermissionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCodeSigningConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateCodeSigningConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateEventSourceMapping(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateEventSourceMappingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFunctionCode(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFunctionCodeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFunctionConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFunctionConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFunctionEventInvokeConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFunctionEventInvokeConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFunctionUrlConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFunctionUrlConfigAsync(array $args = [])
 */
class LambdaClient extends AwsClient
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        $list = $this->getHandlerList();
        if (extension_loaded('curl')) {
            $list->appendInit($this->getDefaultCurlOptionsMiddleware());
        }
    }

    /**
     * Provides a middleware that sets default Curl options for the command
     *
     * @return callable
     */
    public function getDefaultCurlOptionsMiddleware()
    {
        return Middleware::mapCommand(function (CommandInterface $cmd) {
            $defaultCurlOptions = [
                CURLOPT_TCP_KEEPALIVE => 1,
            ];
            if (!isset($cmd['@http']['curl'])) {
                $cmd['@http']['curl'] = $defaultCurlOptions;
            } else {
                $cmd['@http']['curl'] += $defaultCurlOptions;
            }
            return $cmd;
        });
    }
}
