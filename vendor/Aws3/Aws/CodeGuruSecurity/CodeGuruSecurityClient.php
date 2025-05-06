<?php
namespace ClikIT\Infinite_Uploads\Aws\CodeGuruSecurity;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CodeGuru Security** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchGetFindings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchGetFindingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createScan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createScanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createUploadUrl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createUploadUrlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFindings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFindingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMetricsSummary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMetricsSummaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getScan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getScanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFindingsMetrics(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFindingsMetricsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listScans(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listScansAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAccountConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAccountConfigurationAsync(array $args = [])
 */
class CodeGuruSecurityClient extends AwsClient {}
