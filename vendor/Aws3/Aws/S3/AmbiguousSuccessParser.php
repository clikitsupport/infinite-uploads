<?php
namespace ClikIT\Infinite_Uploads\Aws\S3;

use ClikIT\Infinite_Uploads\Aws\Api\Parser\AbstractParser;
use ClikIT\Infinite_Uploads\Aws\Api\Parser\Exception\ParserException;
use ClikIT\Infinite_Uploads\Aws\Api\StructureShape;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use ClikIT\Infinite_Uploads\Aws\Exception\AwsException;
use ClikIT\Infinite_Uploads\Psr\Http\Message\ResponseInterface;
use ClikIT\Infinite_Uploads\Psr\Http\Message\StreamInterface;

/**
 * Converts errors returned with a status code of 200 to a retryable error type.
 *
 * @internal
 */
class AmbiguousSuccessParser extends AbstractParser
{
    private static $ambiguousSuccesses = [
        'UploadPart' => true,
        'UploadPartCopy' => true,
        'CopyObject' => true,
        'CompleteMultipartUpload' => true,
    ];

    /** @var callable */
    private $errorParser;
    /** @var string */
    private $exceptionClass;

    public function __construct(
        callable $parser,
        callable $errorParser,
        $exceptionClass = AwsException::class
    ) {
        $this->parser = $parser;
        $this->errorParser = $errorParser;
        $this->exceptionClass = $exceptionClass;
    }

    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        if (200 === $response->getStatusCode()
            && isset(self::$ambiguousSuccesses[$command->getName()])
        ) {
            $errorParser = $this->errorParser;
            try {
                $parsed = $errorParser($response);
            } catch (ParserException $e) {
                $parsed = [
                    'code' => 'ConnectionError',
                    'message' => "An error connecting to the service occurred"
                        . " while performing the " . $command->getName()
                        . " operation."
                ];
            }
            if (isset($parsed['code']) && isset($parsed['message'])) {
                throw new $this->exceptionClass(
                    $parsed['message'],
                    $command,
                    ['connection_error' => true]
                );
            }
        }

        $fn = $this->parser;
        return $fn($command, $response);
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        return $this->parser->parseMemberFromStream($stream, $member, $response);
    }
}
