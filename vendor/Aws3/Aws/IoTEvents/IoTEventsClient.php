<?php
namespace ClikIT\Infinite_Uploads\Aws\IoTEvents;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Events** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAlarmModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAlarmModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDetectorModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDetectorModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createInput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createInputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAlarmModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAlarmModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDetectorModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDetectorModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteInput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteInputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAlarmModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAlarmModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDetectorModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDetectorModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDetectorModelAnalysis(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDetectorModelAnalysisAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeInput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeInputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLoggingOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLoggingOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDetectorModelAnalysisResults(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDetectorModelAnalysisResultsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAlarmModelVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAlarmModelVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAlarmModels(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAlarmModelsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDetectorModelVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDetectorModelVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDetectorModels(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDetectorModelsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInputRoutings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInputRoutingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInputs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInputsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putLoggingOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putLoggingOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDetectorModelAnalysis(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDetectorModelAnalysisAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAlarmModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAlarmModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDetectorModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDetectorModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateInput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateInputAsync(array $args = [])
 */
class IoTEventsClient extends AwsClient {}
