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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 message implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
class Message implements MessageInterface
{
    /**
     * @var string the default protocol version
     */
    const DEFAUTL_PROTOCOL_VERSION = '1.1';

    /**
     * @var array a map of all registered headers, as original name => array of values
     */
    protected $headers;

    /**
     * @var array a map of lowercase header name => original name
     */
    protected $headerNames;

    /**
     * @var string the HTTP protocol version
     */
    protected $protocolVersion = self::DEFAUTL_PROTOCOL_VERSION;

    /**
     * @var StreamInterface the stream instance
     */
    protected $body;

    /**
     * @var array a map of valid protocol versions
     */
    private static $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
    ];

    /**
     * Constructor.
     *
     * @param array                         $headers the headers
     * @param null|resource|StreamInterface $body    the body
     */
    public function __construct(array $headers = [], $body = null)
    {
        $this->setHeaders($headers);
        $this->body = Stream::create($body);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return [];
        }

        $name = $this->headerNames[$normalized];

        return $this->headers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $value = $this->validateAndTrimHeader($name, $value);
        $normalized = strtolower($name);
        $clone = clone $this;

        if (isset($clone->headerNames[$normalized])) {
            $name = $this->headerNames[$normalized];
            $clone->headers[$name] = array_merge($this->headers[$name], $value);
        } else {
            $clone->headerNames[$normalized] = $name;
            $clone->headers[$name] = $value;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $value = $this->validateAndTrimHeader($name, $value);
        $normalized = strtolower($name);
        $clone = clone $this;

        if (isset($clone->headerNames[$normalized])) {
            unset($clone->headers[$clone->headerNames[$normalized]]);
        }

        $clone->headerNames[$normalized] = $name;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $name = $this->headerNames[$normalized];
        $clone = clone $this;
        unset($clone->headers[$name], $clone->headerNames[$normalized]);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        if (!isset(self::$validProtocolVersions[$version])) {
            throw new \InvalidArgumentException(sprintf('Invalid HTTP version. Must be one of: %s', implode(', ', array_keys(self::$validProtocolVersions))));
        }

        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * Sets mutiple header that complies with RFC 7230.
     *
     * @param array $headers an array of headers
     *
     * @throws \InvalidArgumentException on invalid header name
     * @throws \InvalidArgumentException on invalid header values
     */
    private function setHeaders(array $headers): void
    {
        $this->headerNames = $this->headers = [];

        foreach ($headers as $name => $value) {
            $value = $this->validateAndTrimHeader($name, $value);
            $normalized = strtolower($name);

            if (isset($this->headerNames[$normalized])) {
                $name = $this->headerNames[$normalized];
                $this->headers[$name] = array_merge($this->headers[$name], $value);
            } else {
                $this->headerNames[$normalized] = $name;
                $this->headers[$name] = $value;
            }
        }
    }

    /**
     * Make sure the header complies with RFC 7230.
     *
     * Header names must be a non-empty string consisting of token characters.
     *
     * Header values must be strings consisting of visible characters with all optional
     * leading and trailing whitespace stripped. This method will always strip such
     * optional whitespace. Note that the method does not allow folding whitespace within
     * the values as this was deprecated for almost all instances by the RFC.
     *
     * header-field = field-name ":" OWS field-value OWS
     * field-name   = 1*( "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." / "^"
     *              / "_" / "`" / "|" / "~" / %x30-39 / ( %x41-5A / %x61-7A ) )
     * OWS          = *( SP / HTAB )
     * field-value  = *( ( %x21-7E / %x80-FF ) [ 1*( SP / HTAB ) ( %x21-7E / %x80-FF ) ] )
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     *
     * @param string          $name   the header name
     * @param string|string[] $values the header values
     *
     * @throws \InvalidArgumentException on invalid header name
     * @throws \InvalidArgumentException on invalid header values
     */
    private function validateAndTrimHeader($name, $values): array
    {
        if (!is_string($name) || preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/', $name) !== 1) {
            throw new \InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        $values = is_array($values) ? $values : [$values];

        if (empty($values)) {
            throw new \InvalidArgumentException('Header values must be a string or an array of strings, empty array given.');
        }

        $callback = function ($value) {
            if ((!is_numeric($value) && !is_string($value)) || preg_match('/^[ \t\x21-\x7E\x80-\xFF]*$/', (string) $value) !== 1) {
                throw new \InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            return trim((string) $value, " \t");
        };

        return array_map($callback, array_values($values));
    }
}
