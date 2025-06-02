<?php
namespace ClikIT\Infinite_Uploads\Aws\ObservabilityAdmin;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **CloudWatch Observability Admin Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTelemetryEvaluationStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTelemetryEvaluationStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTelemetryEvaluationStatusForOrganization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTelemetryEvaluationStatusForOrganizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceTelemetry(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceTelemetryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceTelemetryForOrganization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceTelemetryForOrganizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startTelemetryEvaluation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startTelemetryEvaluationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startTelemetryEvaluationForOrganization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startTelemetryEvaluationForOrganizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopTelemetryEvaluation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopTelemetryEvaluationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopTelemetryEvaluationForOrganization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopTelemetryEvaluationForOrganizationAsync(array $args = [])
 */
class ObservabilityAdminClient extends AwsClient {}
