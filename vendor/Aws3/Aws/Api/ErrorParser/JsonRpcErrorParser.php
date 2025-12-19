<?php

namespace ClikIT\Infinite_Uploads\Aws\Api\ErrorParser;

use ClikIT\Infinite_Uploads\Aws\Api\Parser\JsonParser;
use ClikIT\Infinite_Uploads\Aws\Api\Service;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use ClikIT\Infinite_Uploads\Psr\Http\Message\ResponseInterface;
/**
 * Parsers JSON-RPC errors.
 */
class JsonRpcErrorParser extends AbstractErrorParser
{
    use JsonParserTrait;
    private $parser;
    public function __construct(?Service $api = null, ?JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new JsonParser();
    }
    public function __invoke(ResponseInterface $response, ?CommandInterface $command = null)
    {
        $data = $this->genericHandler($response);
        // Make the casing consistent across services.
        if ($data['parsed']) {
            $data['parsed'] = array_change_key_case($data['parsed']);
        }
        if (isset($data['parsed']['__type'])) {
            if (!isset($data['code'])) {
                $parts = explode('#', $data['parsed']['__type']);
                $data['code'] = isset($parts[1]) ? $parts[1] : $parts[0];
            }
            $data['message'] = $data['parsed']['message'] ?? null;
        }
        $this->populateShape($data, $response, $command);
        return $data;
    }
}
