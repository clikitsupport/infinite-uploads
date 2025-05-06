<?php
namespace ClikIT\Infinite_Uploads\Aws\SSO;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Single Sign-On** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRoleCredentials(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRoleCredentialsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccountRoles(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccountRolesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccounts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccountsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result logout(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise logoutAsync(array $args = [])
 */
class SSOClient extends AwsClient {}
