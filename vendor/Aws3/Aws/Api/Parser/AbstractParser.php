<?php
namespace ClikIT\Infinite_Uploads\Aws\Api\Parser;

use ClikIT\Infinite_Uploads\Aws\Api\Service;
use ClikIT\Infinite_Uploads\Aws\Api\StructureShape;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use ClikIT\Infinite_Uploads\Aws\ResultInterface;
use ClikIT\Infinite_Uploads\Psr\Http\Message\ResponseInterface;
use ClikIT\Infinite_Uploads\Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
abstract class AbstractParser
{
    /** @var \ClikIT\Infinite_Uploads\Aws\Api\Service Representation of the service API*/
    protected $api;

    /** @var callable */
    protected $parser;

    /**
     * @param Service $api Service description.
     */
    public function __construct(Service $api)
    {
        $this->api = $api;
    }

    /**
     * @param CommandInterface  $command  Command that was executed.
     * @param ResponseInterface $response Response that was received.
     *
     * @return ResultInterface
     */
    abstract public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    );

    abstract public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    );
}
