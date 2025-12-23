<?php
namespace ClikIT\Infinite_Uploads\Aws\ElasticTranscoder;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic Transcoder** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPreset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPresetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePreset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePresetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobsByPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobsByPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobsByStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobsByStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPipelines(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPresets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPresetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result readJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise readJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result readPipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise readPipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result readPreset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise readPresetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePipeline(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePipelineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePipelineNotifications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePipelineNotificationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePipelineStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePipelineStatusAsync(array $args = [])
 */
class ElasticTranscoderClient extends AwsClient {}
