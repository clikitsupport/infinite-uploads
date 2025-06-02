<?php
namespace ClikIT\Infinite_Uploads\Aws\LicenseManagerLinuxSubscriptions;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS License Manager Linux Subscriptions** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterSubscriptionProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterSubscriptionProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRegisteredSubscriptionProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRegisteredSubscriptionProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLinuxSubscriptionInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLinuxSubscriptionInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLinuxSubscriptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLinuxSubscriptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRegisteredSubscriptionProviders(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRegisteredSubscriptionProvidersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerSubscriptionProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerSubscriptionProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServiceSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServiceSettingsAsync(array $args = [])
 */
class LicenseManagerLinuxSubscriptionsClient extends AwsClient {}
