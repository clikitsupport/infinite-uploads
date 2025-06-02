<?php
namespace ClikIT\Infinite_Uploads\Aws\Account;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Account** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result acceptPrimaryEmailUpdate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise acceptPrimaryEmailUpdateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAlternateContact(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAlternateContactAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableRegion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableRegionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableRegion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableRegionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountInformation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountInformationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAlternateContact(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAlternateContactAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getContactInformation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getContactInformationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPrimaryEmail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPrimaryEmailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRegionOptStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRegionOptStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRegions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRegionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccountName(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccountNameAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAlternateContact(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAlternateContactAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putContactInformation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putContactInformationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startPrimaryEmailUpdate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startPrimaryEmailUpdateAsync(array $args = [])
 */
class AccountClient extends AwsClient {}
