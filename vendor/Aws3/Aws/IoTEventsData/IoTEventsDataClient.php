<?php
namespace ClikIT\Infinite_Uploads\Aws\IoTEventsData;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Events Data** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchAcknowledgeAlarm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchAcknowledgeAlarmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDeleteDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDeleteDetectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDisableAlarm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDisableAlarmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchEnableAlarm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchEnableAlarmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchPutMessage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchPutMessageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchResetAlarm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchResetAlarmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchSnoozeAlarm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchSnoozeAlarmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchUpdateDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchUpdateDetectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAlarm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAlarmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDetector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDetectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAlarms(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAlarmsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDetectors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDetectorsAsync(array $args = [])
 */
class IoTEventsDataClient extends AwsClient {}
