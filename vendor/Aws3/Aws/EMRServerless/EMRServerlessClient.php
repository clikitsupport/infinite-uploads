<?php
namespace ClikIT\Infinite_Uploads\Aws\EMRServerless;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **EMR Serverless** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelJobRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelJobRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDashboardForJobRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDashboardForJobRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getJobRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getJobRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listApplications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobRunAttempts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobRunAttemptsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobRuns(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobRunsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startJobRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startJobRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 */
class EMRServerlessClient extends AwsClient {}
