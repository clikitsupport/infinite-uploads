<?php
namespace ClikIT\Infinite_Uploads\Aws\SageMakerGeospatial;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon SageMaker geospatial capabilities** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEarthObservationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEarthObservationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVectorEnrichmentJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVectorEnrichmentJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportEarthObservationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportEarthObservationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportVectorEnrichmentJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportVectorEnrichmentJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEarthObservationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEarthObservationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRasterDataCollection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRasterDataCollectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVectorEnrichmentJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVectorEnrichmentJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEarthObservationJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEarthObservationJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRasterDataCollections(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRasterDataCollectionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVectorEnrichmentJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVectorEnrichmentJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchRasterDataCollection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchRasterDataCollectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startEarthObservationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startEarthObservationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startVectorEnrichmentJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startVectorEnrichmentJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopEarthObservationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopEarthObservationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopVectorEnrichmentJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopVectorEnrichmentJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SageMakerGeospatialClient extends AwsClient {}
