<?php
namespace ClikIT\Infinite_Uploads\Aws\Invoicing;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Invoicing** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchGetInvoiceProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchGetInvoiceProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createInvoiceUnit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createInvoiceUnitAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteInvoiceUnit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteInvoiceUnitAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInvoiceUnit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInvoiceUnitAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInvoiceUnits(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInvoiceUnitsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateInvoiceUnit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateInvoiceUnitAsync(array $args = [])
 */
class InvoicingClient extends AwsClient {}
