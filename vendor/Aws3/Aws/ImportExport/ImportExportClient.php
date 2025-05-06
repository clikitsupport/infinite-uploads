<?php
namespace ClikIT\Infinite_Uploads\Aws\ImportExport;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Import/Export** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getShippingLabel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getShippingLabelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateJobAsync(array $args = [])
 */
class ImportExportClient extends AwsClient {}
