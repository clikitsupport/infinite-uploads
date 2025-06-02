<?php
namespace ClikIT\Infinite_Uploads\Aws\SocialMessaging;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS End User Messaging Social** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateWhatsAppBusinessAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateWhatsAppBusinessAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteWhatsAppMessageMedia(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteWhatsAppMessageMediaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateWhatsAppBusinessAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateWhatsAppBusinessAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLinkedWhatsAppBusinessAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLinkedWhatsAppBusinessAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLinkedWhatsAppBusinessAccountPhoneNumber(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLinkedWhatsAppBusinessAccountPhoneNumberAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getWhatsAppMessageMedia(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getWhatsAppMessageMediaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLinkedWhatsAppBusinessAccounts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLinkedWhatsAppBusinessAccountsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result postWhatsAppMessageMedia(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise postWhatsAppMessageMediaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putWhatsAppBusinessAccountEventDestinations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putWhatsAppBusinessAccountEventDestinationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendWhatsAppMessage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendWhatsAppMessageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SocialMessagingClient extends AwsClient {}
