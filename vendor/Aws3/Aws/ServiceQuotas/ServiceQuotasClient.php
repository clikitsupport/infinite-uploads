<?php
namespace ClikIT\Infinite_Uploads\Aws\ServiceQuotas;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Service Quotas** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateServiceQuotaTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateServiceQuotaTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServiceQuotaIncreaseRequestFromTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServiceQuotaIncreaseRequestFromTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateServiceQuotaTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateServiceQuotaTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAWSDefaultServiceQuota(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAWSDefaultServiceQuotaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAssociationForServiceQuotaTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAssociationForServiceQuotaTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRequestedServiceQuotaChange(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRequestedServiceQuotaChangeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceQuota(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceQuotaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServiceQuotaIncreaseRequestFromTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServiceQuotaIncreaseRequestFromTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAWSDefaultServiceQuotas(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAWSDefaultServiceQuotasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRequestedServiceQuotaChangeHistory(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRequestedServiceQuotaChangeHistoryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRequestedServiceQuotaChangeHistoryByQuota(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRequestedServiceQuotaChangeHistoryByQuotaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceQuotaIncreaseRequestsInTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceQuotaIncreaseRequestsInTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceQuotas(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceQuotasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putServiceQuotaIncreaseRequestIntoTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putServiceQuotaIncreaseRequestIntoTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result requestServiceQuotaIncrease(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise requestServiceQuotaIncreaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class ServiceQuotasClient extends AwsClient {}
