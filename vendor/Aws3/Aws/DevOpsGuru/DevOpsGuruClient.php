<?php
namespace ClikIT\Infinite_Uploads\Aws\DevOpsGuru;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon DevOps Guru** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result addNotificationChannel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addNotificationChannelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteInsight(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteInsightAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountHealth(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountHealthAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountOverview(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountOverviewAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAnomaly(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAnomalyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventSourcesConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventSourcesConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeFeedback(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeFeedbackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeInsight(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeInsightAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeOrganizationHealth(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeOrganizationHealthAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeOrganizationOverview(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeOrganizationOverviewAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeOrganizationResourceCollectionHealth(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeOrganizationResourceCollectionHealthAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeResourceCollectionHealth(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeResourceCollectionHealthAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServiceIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServiceIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCostEstimation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCostEstimationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourceCollection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourceCollectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAnomaliesForInsight(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAnomaliesForInsightAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAnomalousLogGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAnomalousLogGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInsights(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInsightsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMonitoredResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMonitoredResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listNotificationChannels(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listNotificationChannelsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOrganizationInsights(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOrganizationInsightsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putFeedback(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putFeedbackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeNotificationChannel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeNotificationChannelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchInsights(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchInsightsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchOrganizationInsights(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchOrganizationInsightsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startCostEstimation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startCostEstimationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateEventSourcesConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateEventSourcesConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResourceCollection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResourceCollectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServiceIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServiceIntegrationAsync(array $args = [])
 */
class DevOpsGuruClient extends AwsClient {}
