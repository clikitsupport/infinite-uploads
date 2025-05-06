<?php
namespace ClikIT\Infinite_Uploads\Aws\Translate;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Translate** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createParallelData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createParallelDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteParallelData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteParallelDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTerminology(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTerminologyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTextTranslationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTextTranslationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getParallelData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getParallelDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTerminology(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTerminologyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importTerminology(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importTerminologyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLanguages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLanguagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listParallelData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listParallelDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTerminologies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTerminologiesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTextTranslationJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTextTranslationJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startTextTranslationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startTextTranslationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopTextTranslationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopTextTranslationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result translateDocument(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise translateDocumentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result translateText(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise translateTextAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateParallelData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateParallelDataAsync(array $args = [])
 */
class TranslateClient extends AwsClient {}
