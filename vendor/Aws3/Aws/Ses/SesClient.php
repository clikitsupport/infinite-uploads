<?php
namespace ClikIT\Infinite_Uploads\Aws\Ses;

use ClikIT\Infinite_Uploads\Aws\Api\ApiProvider;
use Aws\Api\DocModel;
use ClikIT\Infinite_Uploads\Aws\Api\Service;
use ClikIT\Infinite_Uploads\Aws\Credentials\CredentialsInterface;

/**
 * This client is used to interact with the **Amazon Simple Email Service (Amazon SES)**.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result cloneReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cloneReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConfigurationSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConfigurationSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConfigurationSetEventDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConfigurationSetEventDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConfigurationSetTrackingOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConfigurationSetTrackingOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCustomVerificationEmailTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createReceiptFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createReceiptFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createReceiptRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createReceiptRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConfigurationSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConfigurationSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConfigurationSetEventDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConfigurationSetEventDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConfigurationSetTrackingOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConfigurationSetTrackingOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCustomVerificationEmailTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteIdentityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteIdentityPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteIdentityPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteReceiptFilter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteReceiptFilterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteReceiptRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteReceiptRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVerifiedEmailAddress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVerifiedEmailAddressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeActiveReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeActiveReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeConfigurationSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeConfigurationSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeReceiptRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeReceiptRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountSendingEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountSendingEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCustomVerificationEmailTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIdentityDkimAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIdentityDkimAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIdentityMailFromDomainAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIdentityMailFromDomainAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIdentityNotificationAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIdentityNotificationAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIdentityPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIdentityPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIdentityVerificationAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIdentityVerificationAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSendQuota(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSendQuotaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSendStatistics(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSendStatisticsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConfigurationSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConfigurationSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCustomVerificationEmailTemplates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCustomVerificationEmailTemplatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listIdentities(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listIdentitiesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listIdentityPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listIdentityPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listReceiptFilters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listReceiptFiltersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listReceiptRuleSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listReceiptRuleSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTemplates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTemplatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVerifiedEmailAddresses(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVerifiedEmailAddressesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putConfigurationSetDeliveryOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putConfigurationSetDeliveryOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putIdentityPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putIdentityPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result reorderReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise reorderReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendBounce(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendBounceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendBulkTemplatedEmail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendBulkTemplatedEmailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendCustomVerificationEmail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendCustomVerificationEmailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendEmail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendEmailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendRawEmail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendRawEmailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendTemplatedEmail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendTemplatedEmailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setActiveReceiptRuleSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setActiveReceiptRuleSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setIdentityDkimEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setIdentityDkimEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setIdentityFeedbackForwardingEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setIdentityFeedbackForwardingEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setIdentityHeadersInNotificationsEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setIdentityHeadersInNotificationsEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setIdentityMailFromDomain(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setIdentityMailFromDomainAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setIdentityNotificationTopic(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setIdentityNotificationTopicAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setReceiptRulePosition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setReceiptRulePositionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testRenderTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testRenderTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAccountSendingEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAccountSendingEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationSetEventDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationSetEventDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationSetReputationMetricsEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationSetReputationMetricsEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationSetSendingEnabled(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationSetSendingEnabledAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationSetTrackingOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationSetTrackingOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCustomVerificationEmailTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateReceiptRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateReceiptRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyDomainDkim(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyDomainDkimAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyDomainIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyDomainIdentityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyEmailAddress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyEmailAddressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyEmailIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyEmailIdentityAsync(array $args = [])
 */
class SesClient extends \Aws\AwsClient
{
    /**
     * @deprecated This method will no longer work due to the deprecation of
     *             V2 credentials with SES as of 03/25/2021
     * Create an SMTP password for a given IAM user's credentials.
     *
     * The SMTP username is the Access Key ID for the provided credentials.
     *
     * @link http://docs.aws.amazon.com/ses/latest/DeveloperGuide/smtp-credentials.html#smtp-credentials-convert
     *
     * @param CredentialsInterface $creds
     *
     * @return string
     */
    public static function generateSmtpPassword(CredentialsInterface $creds)
    {
        static $version = "\x02";
        static $algo = 'sha256';
        static $message = 'SendRawEmail';
        $signature = hash_hmac($algo, $message, $creds->getSecretKey(), true);

        return base64_encode($version . $signature);
    }

    /**
     * Create an SMTP password for a given IAM user's credentials.
     *
     * The SMTP username is the Access Key ID for the provided credentials. This
     * utility method is not guaranteed to work indefinitely and is provided as
     * a convenience to customers using the generateSmtpPassword method.  It is
     * not recommended for use in production
     *
     * @link https://docs.aws.amazon.com/ses/latest/DeveloperGuide/smtp-credentials.html#smtp-credentials-convert
     *
     * @param CredentialsInterface $creds
     * @param string $region
     *
     * @return string
     */
    public static function generateSmtpPasswordV4(CredentialsInterface $creds, $region)
    {
        $key = $creds->getSecretKey();

        $date = "11111111";
        $service = "ses";
        $terminal = "aws4_request";
        $message = "SendRawEmail";
        $version = 0x04;

        $signature = self::sign($date, "AWS4" . $key);
        $signature = self::sign($region, $signature);
        $signature = self::sign($service, $signature);
        $signature = self::sign($terminal, $signature);
        $signature = self::sign($message, $signature);
        $signatureAndVersion = pack('c', $version) . $signature;

        return  base64_encode($signatureAndVersion);
    }

    private static function sign($key, $message) {
        return hash_hmac('sha256', $key, $message, true);
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        $b64 = '<div class="alert alert-info">This value will be base64 encoded on your behalf.</div>';

        $docs['shapes']['RawMessage']['append'] = $b64;

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }
}
