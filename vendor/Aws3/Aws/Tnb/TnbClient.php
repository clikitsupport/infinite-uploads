<?php
namespace ClikIT\Infinite_Uploads\Aws\Tnb;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Telco Network Builder** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelSolNetworkOperation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelSolNetworkOperationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSolFunctionPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSolFunctionPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSolNetworkInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSolNetworkInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSolNetworkPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSolNetworkPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSolFunctionPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSolFunctionPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSolNetworkInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSolNetworkInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSolNetworkPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSolNetworkPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolFunctionInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolFunctionInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolFunctionPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolFunctionPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolFunctionPackageContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolFunctionPackageContentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolFunctionPackageDescriptor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolFunctionPackageDescriptorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolNetworkInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolNetworkInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolNetworkOperation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolNetworkOperationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolNetworkPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolNetworkPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolNetworkPackageContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolNetworkPackageContentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSolNetworkPackageDescriptor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSolNetworkPackageDescriptorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result instantiateSolNetworkInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise instantiateSolNetworkInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSolFunctionInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSolFunctionInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSolFunctionPackages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSolFunctionPackagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSolNetworkInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSolNetworkInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSolNetworkOperations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSolNetworkOperationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSolNetworkPackages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSolNetworkPackagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putSolFunctionPackageContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putSolFunctionPackageContentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putSolNetworkPackageContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putSolNetworkPackageContentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result terminateSolNetworkInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise terminateSolNetworkInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSolFunctionPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSolFunctionPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSolNetworkInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSolNetworkInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSolNetworkPackage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSolNetworkPackageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result validateSolFunctionPackageContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise validateSolFunctionPackageContentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result validateSolNetworkPackageContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise validateSolNetworkPackageContentAsync(array $args = [])
 */
class TnbClient extends AwsClient {}
