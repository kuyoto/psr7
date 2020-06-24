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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 server-side request implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class ServerRequest extends Message implements ServerRequestInterface
{
    use RequestTrait;

    /**
     * @var array the attributes
     */
    private $attributes = [];

    /**
     * @var array the cookie parameters
     */
    private $cookiesParams = [];

    /**
     * @var null|array|object the parsed body
     */
    private $parsedBody;

    /**
     * @var array the query parameters
     */
    private $queryParams = [];

    /**
     * @var array the server parameters
     */
    private $serverParams = [];

    /**
     * @var UploadedFileInterface[] a list of uploaded file intances
     */
    private $uploadedFiles = [];

    /**
     * Constructor.
     *
     * @param string                        $method       the HTTP request method
     * @param string|UriInterface           $uri          the uri
     * @param array                         $headers      the request headers
     * @param null|resource|StreamInterface $body         the request body
     * @param array                         $serverParams the server params of the request
     */
    public function __construct(string $method, $uri, $headers = [], $body = null, array $serverParams = [])
    {
        $this->method = $this->filterMethod($method);

        $this->uri = !$uri instanceof UriInterface ? new Uri($uri) : $uri;

        parent::__construct($headers, $body);

        if (!isset($this->headerNames['host']) || $this->uri->getHost() !== '') {
            $this->updateHostFromUri();
        }

        $this->serverParams = $serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes) === false) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookiesParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookiesParams = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new \InvalidArgumentException('Invalid argument passed.');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }
}
