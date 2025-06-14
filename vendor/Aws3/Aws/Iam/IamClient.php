<?php
namespace ClikIT\Infinite_Uploads\Aws\Iam;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Identity and Access Management (AWS IAM)** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result addClientIDToOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addClientIDToOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addRoleToInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addRoleToInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addUserToGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addUserToGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result attachGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise attachGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result attachRolePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise attachRolePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result attachUserPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise attachUserPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result changePassword(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise changePasswordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccessKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccessKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccountAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccountAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLoginProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLoginProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPolicyVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPolicyVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSAMLProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSAMLProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createServiceLinkedRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createServiceLinkedRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createServiceSpecificCredential(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createServiceSpecificCredentialAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createVirtualMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createVirtualMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deactivateMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deactivateMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccountAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccountAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccountPasswordPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccountPasswordPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLoginProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLoginProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePolicyVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePolicyVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRolePermissionsBoundary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRolePermissionsBoundaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRolePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRolePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSAMLProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSAMLProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSSHPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSSHPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServerCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServerCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServiceLinkedRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServiceLinkedRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServiceSpecificCredential(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServiceSpecificCredentialAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSigningCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSigningCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteUserPermissionsBoundary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteUserPermissionsBoundaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteUserPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteUserPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVirtualMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVirtualMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result detachGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise detachGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result detachRolePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise detachRolePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result detachUserPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise detachUserPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableOrganizationsRootCredentialsManagement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableOrganizationsRootCredentialsManagementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableOrganizationsRootSessions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableOrganizationsRootSessionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableOrganizationsRootCredentialsManagement(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableOrganizationsRootCredentialsManagementAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableOrganizationsRootSessions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableOrganizationsRootSessionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateCredentialReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateCredentialReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateOrganizationsAccessReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateOrganizationsAccessReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateServiceLastAccessedDetails(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateServiceLastAccessedDetailsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessKeyLastUsed(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessKeyLastUsedAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountAuthorizationDetails(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountAuthorizationDetailsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountPasswordPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountPasswordPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountSummary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountSummaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getContextKeysForCustomPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getContextKeysForCustomPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getContextKeysForPrincipalPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getContextKeysForPrincipalPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCredentialReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCredentialReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLoginProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLoginProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOrganizationsAccessReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOrganizationsAccessReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPolicyVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPolicyVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRolePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRolePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSAMLProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSAMLProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSSHPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSSHPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServerCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServerCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceLastAccessedDetails(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceLastAccessedDetailsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceLastAccessedDetailsWithEntities(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceLastAccessedDetailsWithEntitiesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceLinkedRoleDeletionStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceLinkedRoleDeletionStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getUserPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getUserPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessKeysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccountAliases(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccountAliasesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAttachedGroupPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAttachedGroupPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAttachedRolePolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAttachedRolePoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAttachedUserPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAttachedUserPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEntitiesForPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEntitiesForPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGroupPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGroupPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGroupsForUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGroupsForUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInstanceProfileTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInstanceProfileTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInstanceProfiles(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInstanceProfilesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInstanceProfilesForRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInstanceProfilesForRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMFADeviceTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMFADeviceTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMFADevices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMFADevicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOpenIDConnectProviderTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOpenIDConnectProviderTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOpenIDConnectProviders(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOpenIDConnectProvidersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOrganizationsFeatures(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOrganizationsFeaturesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPoliciesGrantingServiceAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPoliciesGrantingServiceAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPolicyTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPolicyTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPolicyVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPolicyVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRolePolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRolePoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRoleTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRoleTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRoles(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRolesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSAMLProviderTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSAMLProviderTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSAMLProviders(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSAMLProvidersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSSHPublicKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSSHPublicKeysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServerCertificateTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServerCertificateTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServerCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServerCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceSpecificCredentials(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceSpecificCredentialsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSigningCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSigningCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listUserPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listUserPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listUserTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listUserTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listUsers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listUsersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVirtualMFADevices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVirtualMFADevicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRolePermissionsBoundary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRolePermissionsBoundaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRolePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRolePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putUserPermissionsBoundary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putUserPermissionsBoundaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putUserPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putUserPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeClientIDFromOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeClientIDFromOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeRoleFromInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeRoleFromInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeUserFromGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeUserFromGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resetServiceSpecificCredential(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resetServiceSpecificCredentialAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resyncMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resyncMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setDefaultPolicyVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setDefaultPolicyVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setSecurityTokenServicePreferences(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setSecurityTokenServicePreferencesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result simulateCustomPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise simulateCustomPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result simulatePrincipalPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise simulatePrincipalPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagSAMLProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagSAMLProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagServerCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagServerCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagInstanceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagInstanceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagMFADevice(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagMFADeviceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagOpenIDConnectProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagOpenIDConnectProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagSAMLProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagSAMLProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagServerCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagServerCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAccessKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAccessKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAccountPasswordPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAccountPasswordPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssumeRolePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssumeRolePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLoginProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLoginProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateOpenIDConnectProviderThumbprint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateOpenIDConnectProviderThumbprintAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRoleDescription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRoleDescriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSAMLProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSAMLProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSSHPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSSHPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServerCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServerCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServiceSpecificCredential(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServiceSpecificCredentialAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSigningCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSigningCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadSSHPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadSSHPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadServerCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadServerCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadSigningCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadSigningCertificateAsync(array $args = [])
 */
class IamClient extends AwsClient {}
