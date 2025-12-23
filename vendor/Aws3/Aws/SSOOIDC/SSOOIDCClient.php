<?php
namespace ClikIT\Infinite_Uploads\Aws\SSOOIDC;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS SSO OIDC** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createToken(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTokenAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTokenWithIAM(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTokenWithIAMAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerClient(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerClientAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDeviceAuthorization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDeviceAuthorizationAsync(array $args = [])
 */
class SSOOIDCClient extends AwsClient {}
