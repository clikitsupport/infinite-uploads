<?php
namespace ClikIT\Infinite_Uploads\Aws\ChimeSDKMeetings;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Chime SDK Meetings** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchCreateAttendee(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchCreateAttendeeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchUpdateAttendeeCapabilitiesExcept(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchUpdateAttendeeCapabilitiesExceptAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAttendee(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAttendeeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMeeting(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMeetingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMeetingWithAttendees(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMeetingWithAttendeesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAttendee(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAttendeeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMeeting(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMeetingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAttendee(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAttendeeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMeeting(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMeetingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAttendees(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAttendeesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startMeetingTranscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startMeetingTranscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopMeetingTranscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopMeetingTranscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAttendeeCapabilities(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAttendeeCapabilitiesAsync(array $args = [])
 */
class ChimeSDKMeetingsClient extends AwsClient {}
