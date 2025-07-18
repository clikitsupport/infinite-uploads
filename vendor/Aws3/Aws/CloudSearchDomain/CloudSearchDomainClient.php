<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudSearchDomain;

use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\CommandInterface;
use ClikIT\Infinite_Uploads\Aws\HandlerList;
use ClikIT\Infinite_Uploads\GuzzleHttp\Psr7\Uri;
use ClikIT\Infinite_Uploads\Psr\Http\Message\RequestInterface;
use ClikIT\Infinite_Uploads\GuzzleHttp\Psr7;

/**
 * This client is used to search and upload documents to an **Amazon CloudSearch** Domain.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result search(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result suggest(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise suggestAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadDocuments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadDocumentsAsync(array $args = [])
 */
class CloudSearchDomainClient extends AwsClient
{
    public function __construct(array $args)
    {
        parent::__construct($args);
        $list = $this->getHandlerList();
        $list->appendBuild($this->searchByPost(), 'cloudsearchdomain.search_by_POST');
    }

    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['endpoint']['required'] = true;
        $args['region']['default'] = function (array $args) {
            // Determine the region from the provided endpoint.
            // (e.g. http://search-blah.{region}.cloudsearch.amazonaws.com)
            return explode('.', new Uri($args['endpoint']))[1];
        };
        unset($args['endpoint']['default']);

        return $args;
    }

    /**
     * Use POST for search command
     *
     * Useful when query string is too long
     */
    private function searchByPost()
    {
        return static function (callable $handler) {
            return function (
                CommandInterface $c,
                ?RequestInterface $r = null
            ) use ($handler) {
                if ($c->getName() !== 'Search') {
                    return $handler($c, $r);
                }
                return $handler($c, self::convertGetToPost($r));
            };
        };
    }

    /**
     * Converts default GET request to a POST request
     *
     * Avoiding length restriction in query
     *
     * @param RequestInterface $r GET request to be converted
     * @return RequestInterface $req converted POST request
     */
    public static function convertGetToPost(RequestInterface $r)
    {
        if ($r->getMethod() === 'POST') {
            return $r;
        }

        $query = $r->getUri()->getQuery();
        $req = $r->withMethod('POST')
            ->withBody(Psr7\Utils::streamFor($query))
            ->withHeader('Content-Length', strlen($query))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withUri($r->getUri()->withQuery(''));
        return $req;
    }
}
