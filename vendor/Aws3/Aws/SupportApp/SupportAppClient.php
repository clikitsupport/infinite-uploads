<?php
namespace ClikIT\Infinite_Uploads\Aws\SupportApp;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Support App** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSlackChannelConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSlackChannelConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccountAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccountAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSlackChannelConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSlackChannelConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSlackWorkspaceConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSlackWorkspaceConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSlackChannelConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSlackChannelConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSlackWorkspaceConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSlackWorkspaceConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccountAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccountAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerSlackWorkspaceForOrganization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerSlackWorkspaceForOrganizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSlackChannelConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSlackChannelConfigurationAsync(array $args = [])
 */
class SupportAppClient extends AwsClient {}
