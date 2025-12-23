<?php
namespace ClikIT\Infinite_Uploads\Aws\ApplicationCostProfiler;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Application Cost Profiler** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteReportDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteReportDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReportDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReportDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importApplicationUsage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importApplicationUsageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listReportDefinitions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listReportDefinitionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putReportDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putReportDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateReportDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateReportDefinitionAsync(array $args = [])
 */
class ApplicationCostProfilerClient extends AwsClient {}
