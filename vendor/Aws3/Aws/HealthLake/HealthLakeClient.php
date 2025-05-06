<?php
namespace ClikIT\Infinite_Uploads\Aws\HealthLake;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon HealthLake** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFHIRDatastore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFHIRDatastoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFHIRDatastore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFHIRDatastoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeFHIRDatastore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeFHIRDatastoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeFHIRExportJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeFHIRExportJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeFHIRImportJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeFHIRImportJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFHIRDatastores(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFHIRDatastoresAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFHIRExportJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFHIRExportJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFHIRImportJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFHIRImportJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startFHIRExportJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startFHIRExportJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startFHIRImportJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startFHIRImportJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class HealthLakeClient extends AwsClient {}
