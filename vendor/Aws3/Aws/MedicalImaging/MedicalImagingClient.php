<?php
namespace ClikIT\Infinite_Uploads\Aws\MedicalImaging;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Health Imaging** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result copyImageSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise copyImageSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDatastore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDatastoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDatastore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDatastoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteImageSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteImageSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDICOMImportJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDICOMImportJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDatastore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDatastoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getImageFrame(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getImageFrameAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getImageSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getImageSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getImageSetMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getImageSetMetadataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDICOMImportJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDICOMImportJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDatastores(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDatastoresAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listImageSetVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listImageSetVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchImageSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchImageSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDICOMImportJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDICOMImportJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateImageSetMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateImageSetMetadataAsync(array $args = [])
 */
class MedicalImagingClient extends AwsClient {}
