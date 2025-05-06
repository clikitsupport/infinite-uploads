<?php
namespace ClikIT\Infinite_Uploads\Aws\LicenseManagerUserSubscriptions;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS License Manager User Subscriptions** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLicenseServerEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLicenseServerEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLicenseServerEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLicenseServerEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterIdentityProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterIdentityProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listIdentityProviders(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listIdentityProvidersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLicenseServerEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLicenseServerEndpointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listProductSubscriptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listProductSubscriptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listUserAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listUserAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerIdentityProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerIdentityProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startProductSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startProductSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopProductSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopProductSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateIdentityProviderSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateIdentityProviderSettingsAsync(array $args = [])
 */
class LicenseManagerUserSubscriptionsClient extends AwsClient {}
