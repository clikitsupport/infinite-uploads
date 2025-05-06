<?php
namespace ClikIT\Infinite_Uploads\Aws\IdentityStore;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS SSO Identity Store** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGroupMembership(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGroupMembershipAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGroupMembership(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGroupMembershipAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeGroupMembership(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeGroupMembershipAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGroupId(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGroupIdAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGroupMembershipId(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGroupMembershipIdAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getUserId(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getUserIdAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result isMemberInGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise isMemberInGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGroupMemberships(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGroupMembershipsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGroupMembershipsForMember(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGroupMembershipsForMemberAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listUsers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listUsersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateUserAsync(array $args = [])
 */
class IdentityStoreClient extends AwsClient {}
