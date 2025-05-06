<?php
namespace ClikIT\Infinite_Uploads\Aws\Glacier;

use ClikIT\Infinite_Uploads\Aws\Api\ApiProvider;
use Aws\Api\DocModel;
use ClikIT\Infinite_Uploads\Aws\Api\Service;
use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use ClikIT\Infinite_Uploads\Aws\Exception\CouldNotCreateChecksumException;
use ClikIT\Infinite_Uploads\Aws\HashingStream;
use ClikIT\Infinite_Uploads\Aws\Middleware;
use ClikIT\Infinite_Uploads\Aws\PhpHash;
use ClikIT\Infinite_Uploads\Psr\Http\Message\RequestInterface;

/**
 * This client is used to interact with the **Amazon Glacier** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result abortMultipartUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise abortMultipartUploadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result abortVaultLock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise abortVaultLockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addTagsToVault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addTagsToVaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result completeMultipartUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise completeMultipartUploadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result completeVaultLock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise completeVaultLockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createVault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createVaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteArchive(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteArchiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVaultAccessPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVaultAccessPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVaultNotifications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVaultNotificationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeVault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeVaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDataRetrievalPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDataRetrievalPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getJobOutput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getJobOutputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVaultAccessPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVaultAccessPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVaultLock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVaultLockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVaultNotifications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVaultNotificationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result initiateJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise initiateJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result initiateMultipartUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise initiateMultipartUploadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result initiateVaultLock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise initiateVaultLockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMultipartUploads(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMultipartUploadsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listParts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPartsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listProvisionedCapacity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listProvisionedCapacityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForVault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForVaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVaults(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVaultsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result purchaseProvisionedCapacity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise purchaseProvisionedCapacityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeTagsFromVault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeTagsFromVaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setDataRetrievalPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setDataRetrievalPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setVaultAccessPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setVaultAccessPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setVaultNotifications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setVaultNotificationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadArchive(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadArchiveAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadMultipartPart(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadMultipartPartAsync(array $args = [])
 */
class GlacierClient extends AwsClient
{
    public function __construct(array $args)
    {
        parent::__construct($args);

        // Setup middleware.
        $stack = $this->getHandlerList();
        $stack->appendBuild($this->getApiVersionMiddleware(), 'glacier.api_version');
        $stack->appendBuild($this->getChecksumsMiddleware(), 'glacier.checksum');
        $stack->appendBuild(
            Middleware::contentType(['UploadArchive', 'UploadPart']),
            'glacier.content_type'
        );
        $stack->appendInit(
            Middleware::sourceFile($this->getApi(), 'body', 'sourceFile'),
            'glacier.source_file'
        );
    }

    /**
     * {@inheritdoc}
     *
     * Sets the default accountId to "-" for all operations.
     */
    public function getCommand($name, array $args = [])
    {
        return parent::getCommand($name, $args + ['accountId' => '-']);
    }

    /**
     * Creates a middleware that updates a command with the content and tree
     * hash headers for upload operations.
     *
     * @return callable
     * @throws CouldNotCreateChecksumException if the body is not seekable.
     */
    private function getChecksumsMiddleware()
    {
        return function (callable $handler) {
            return function (
                CommandInterface $command,
                ?RequestInterface $request = null
            ) use ($handler) {
                // Accept "ContentSHA256" with a lowercase "c" to match other Glacier params.
                if (!$command['ContentSHA256'] && $command['contentSHA256']) {
                    $command['ContentSHA256'] = $command['contentSHA256'];
                    unset($command['contentSHA256']);
                }

                // If uploading, then make sure checksums are added.
                $name = $command->getName();
                if (($name === 'UploadArchive' || $name === 'UploadMultipartPart')
                    && (!$command['checksum'] || !$command['ContentSHA256'])
                ) {
                    $body = $request->getBody();
                    if (!$body->isSeekable()) {
                        throw new CouldNotCreateChecksumException('sha256');
                    }

                    // Add a tree hash if not provided.
                    if (!$command['checksum']) {
                        $body = new HashingStream(
                            $body, new TreeHash(),
                            function ($result) use (&$request) {
                                $request = $request->withHeader(
                                    'x-amz-sha256-tree-hash',
                                    bin2hex($result)
                                );
                            }
                        );
                    }

                    // Add a linear content hash if not provided.
                    if (!$command['ContentSHA256']) {
                        $body = new HashingStream(
                            $body, new PhpHash('sha256'),
                            function ($result) use ($command) {
                                $command['ContentSHA256'] = bin2hex($result);
                            }
                        );
                    }

                    // Read the stream in order to calculate the hashes.
                    while (!$body->eof()) {
                        $body->read(1048576);
                    }
                    $body->seek(0);
                }

                // Set the content hash header if a value is in the command.
                if ($command['ContentSHA256']) {
                    $request = $request->withHeader(
                        'x-amz-content-sha256',
                        $command['ContentSHA256']
                    );
                }

                return $handler($command, $request);
            };
        };
    }

    /**
     * Creates a middleware that adds the API version header for all requests.
     *
     * @return callable
     */
    private function getApiVersionMiddleware()
    {
        return function (callable $handler) {
            return function (
                CommandInterface $command,
                ?RequestInterface $request = null
            ) use ($handler) {
                return $handler($command, $request->withHeader(
                    'x-amz-glacier-version',
                    $this->getApi()->getMetadata('apiVersion')
                ));
            };
        };
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        // Add the SourceFile parameter.
        $docs['shapes']['SourceFile']['base'] = 'The path to a file on disk to use instead of the body parameter.';
        $api['shapes']['SourceFile'] = ['type' => 'string'];
        $api['shapes']['UploadArchiveInput']['members']['sourceFile'] = ['shape' => 'SourceFile'];
        $api['shapes']['UploadMultipartPartInput']['members']['sourceFile'] = ['shape' => 'SourceFile'];

        // Add the ContentSHA256 parameter.
        $docs['shapes']['ContentSHA256']['base'] = 'A SHA256 hash of the content of the request body';
        $api['shapes']['ContentSHA256'] = ['type' => 'string'];
        $api['shapes']['UploadArchiveInput']['members']['contentSHA256'] = ['shape' => 'ContentSHA256'];
        $api['shapes']['UploadMultipartPartInput']['members']['contentSHA256'] = ['shape' => 'ContentSHA256'];

        // Add information about "checksum" and "ContentSHA256" being optional.
        $optional = '<div class="alert alert-info">The SDK will compute this value '
            . 'for you on your behalf if it is not supplied.</div>';
        $docs['shapes']['checksum']['append'] = $optional;
        $docs['shapes']['ContentSHA256']['append'] = $optional;

        // Make "accountId" optional for all operations.
        foreach ($api['operations'] as $operation) {
            $inputShape =& $api['shapes'][$operation['input']['shape']];
            $accountIdIndex = array_search('accountId', $inputShape['required']);
            unset($inputShape['required'][$accountIdIndex]);
        }
        // Add information about the default value for "accountId".
        $optional = '<div class="alert alert-info">The SDK will set this value to "-" by default.</div>';
        foreach ($docs['shapes']['string']['refs'] as $name => &$ref) {
            if (strpos($name, 'accountId')) {
                $ref .= $optional;
            }
        }

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }
}
