<?php
namespace ClikIT\Infinite_Uploads\Aws\TrustedAdvisor;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **TrustedAdvisor Public API** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchUpdateRecommendationResourceExclusion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchUpdateRecommendationResourceExclusionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOrganizationRecommendation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOrganizationRecommendationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRecommendation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRecommendationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listChecks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listChecksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOrganizationRecommendationAccounts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOrganizationRecommendationAccountsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOrganizationRecommendationResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOrganizationRecommendationResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOrganizationRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOrganizationRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecommendationResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecommendationResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateOrganizationRecommendationLifecycle(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateOrganizationRecommendationLifecycleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRecommendationLifecycle(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRecommendationLifecycleAsync(array $args = [])
 */
class TrustedAdvisorClient extends AwsClient {}
