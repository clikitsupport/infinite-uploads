<?php
namespace ClikIT\Infinite_Uploads\Aws\EC2InstanceConnect;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS EC2 Instance Connect** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendSSHPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendSSHPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendSerialConsoleSSHPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendSerialConsoleSSHPublicKeyAsync(array $args = [])
 */
class EC2InstanceConnectClient extends AwsClient {}
