<?php
namespace ClikIT\Infinite_Uploads\Aws\OSIS;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon OpenSearch Ingestion** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPipelineBlueprint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPipelineBlueprintAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPipelineChangeProgress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPipelineChangeProgressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPipelineBlueprints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPipelineBlueprintsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPipelines(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result validatePipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise validatePipelineAsync(array $args = [])
 */
class OSISClient extends AwsClient {}
