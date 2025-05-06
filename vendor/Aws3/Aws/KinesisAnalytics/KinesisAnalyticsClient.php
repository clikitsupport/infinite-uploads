<?php
namespace ClikIT\Infinite_Uploads\Aws\KinesisAnalytics;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Analytics** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result addApplicationCloudWatchLoggingOption(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addApplicationCloudWatchLoggingOptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addApplicationInput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addApplicationInputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addApplicationInputProcessingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addApplicationInputProcessingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addApplicationOutput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addApplicationOutputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addApplicationReferenceDataSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addApplicationReferenceDataSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApplicationCloudWatchLoggingOption(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApplicationCloudWatchLoggingOptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApplicationInputProcessingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApplicationInputProcessingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApplicationOutput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApplicationOutputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApplicationReferenceDataSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApplicationReferenceDataSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result discoverInputSchema(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise discoverInputSchemaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listApplications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 */
class KinesisAnalyticsClient extends AwsClient {}
