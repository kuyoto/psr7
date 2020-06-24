<?php

/**
 * PSR-7 (https://github.com/kuyoto/psr7).
 *
 * PHP version 7
 *
 * @category  Library
 *
 * @author    Tolulope Kuyoro <nifskid1999@gmail.com>
 * @copyright 2020 Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license   https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Kuyoto\Psr7;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 request implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class Request extends Message implements RequestInterface
{
    use RequestTrait;

    /**
     * Constructor.
     *
     * @param string                        $method  the HTTP request method
     * @param string|UriInterface           $uri     the uri
     * @param array                         $headers the request headers
     * @param null|resource|StreamInterface $body    the request body
     */
    public function __construct(string $method, $uri, array $headers = [], $body = null)
    {
        $this->method = $this->filterMethod($method);

        $this->uri = !$uri instanceof UriInterface ? new Uri($uri) : $uri;

        parent::__construct($headers, $body);

        if (!isset($this->headerNames['host']) || $this->uri->getHost() !== '') {
            $this->updateHostFromUri();
        }
    }
}
