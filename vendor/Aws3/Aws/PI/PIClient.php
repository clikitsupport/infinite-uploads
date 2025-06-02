<?php
namespace ClikIT\Infinite_Uploads\Aws\PI;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Performance Insights** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPerformanceAnalysisReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPerformanceAnalysisReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePerformanceAnalysisReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePerformanceAnalysisReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDimensionKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDimensionKeysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDimensionKeyDetails(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDimensionKeyDetailsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPerformanceAnalysisReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPerformanceAnalysisReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourceMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourceMetadataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourceMetrics(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourceMetricsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAvailableResourceDimensions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAvailableResourceDimensionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAvailableResourceMetrics(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAvailableResourceMetricsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPerformanceAnalysisReports(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPerformanceAnalysisReportsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class PIClient extends AwsClient {}
