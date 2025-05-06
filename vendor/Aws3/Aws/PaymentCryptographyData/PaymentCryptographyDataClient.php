<?php
namespace ClikIT\Infinite_Uploads\Aws\PaymentCryptographyData;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Payment Cryptography Data Plane** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result decryptData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise decryptDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result encryptData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise encryptDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateCardValidationData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateCardValidationDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateMac(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateMacAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateMacEmvPinChange(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateMacEmvPinChangeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generatePinData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generatePinDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result reEncryptData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise reEncryptDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result translatePinData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise translatePinDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyAuthRequestCryptogram(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyAuthRequestCryptogramAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyCardValidationData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyCardValidationDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyMac(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyMacAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyPinData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyPinDataAsync(array $args = [])
 */
class PaymentCryptographyDataClient extends AwsClient {}
