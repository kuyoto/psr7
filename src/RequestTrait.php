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

use Psr\Http\Message\UriInterface;

/**
 * Trait with common request behaviors.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 */
trait RequestTrait
{
    /**
     * @var string the HTTP request method
     */
    private $method;

    /**
     * @var null|string the request target
     */
    private $requestTarget;

    /**
     * @var UriInterface the uri instance
     */
    private $uri;

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($target == '') {
            $target = '/';
        }

        if ($this->uri->getQuery() !== '') {
            $target .= sprintf('?', $this->uri->getQuery());
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $this->filterMethod($method);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        if ($requestTarget === $this->requestTarget) {
            return $this;
        }

        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException('The request target provided cannot contain whitespace');
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost || !isset($this->headerNames['host'])) {
            $clone->updateHostFromUri();
        }

        return $clone;
    }

    /**
     * Validate the HTTP method.
     *
     * @param null|string $method the HTTP request method
     *
     * @throws \InvalidArgumentException on invalid HTTP method
     */
    private function filterMethod($method): string
    {
        if (!is_string($method)) {
            throw new \InvalidArgumentException('HTTP method must be a string');
        }

        if (!preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]+$/i", $method)) {
            throw new \InvalidArgumentException(sprintf('Unsupported HTTP method "%s" provided', $method));
        }

        return $method;
    }

    /**
     * Updates the Host header from the uri.
     */
    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();

        if ($host == '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= sprintf(':%d', $port);
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $header = 'Host';
            $this->headerNames['host'] = 'Host';
        }
        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers;
    }
}
