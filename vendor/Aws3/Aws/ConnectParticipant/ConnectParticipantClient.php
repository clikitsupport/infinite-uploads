<?php
namespace ClikIT\Infinite_Uploads\Aws\ConnectParticipant;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Connect Participant Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelParticipantAuthentication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelParticipantAuthenticationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result completeAttachmentUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise completeAttachmentUploadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createParticipantConnection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createParticipantConnectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeView(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeViewAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disconnectParticipant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disconnectParticipantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAttachment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAttachmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAuthenticationUrl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAuthenticationUrlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTranscript(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTranscriptAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendEvent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendEventAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendMessage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendMessageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startAttachmentUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startAttachmentUploadAsync(array $args = [])
 */
class ConnectParticipantClient extends AwsClient {}
