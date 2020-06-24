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
 * PSR-7 URI implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class Uri implements UriInterface
{
    /**
     * @var string sub-delimiters used in user info, query strings and fragments
     */
    public const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * @var string unreserved characters used in user info, paths, query strings, and fragments
     */
    public const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';

    /**
     * @var array an array of allowed schemes
     */
    private const SCHEMES = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * @var string the uri scheme (without "://" suffix)
     */
    private $scheme = '';

    /**
     * @var string the uri user info (user and password)
     */
    private $userInfo = '';

    /**
     * @var string the uri host
     */
    private $host = '';

    /**
     * @var int the uri port
     */
    private $port;

    /**
     * @var string the uri path
     */
    private $path = '';

    /**
     * @var string the uri query string (without "?" prefix)
     */
    private $query = '';

    /**
     * @var string the uri fragment (without "#" prefix)
     */
    private $fragment = '';

    /**
     * Constructor.
     *
     * @param string $uri the uri
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $uri)
    {
        if ($uri !== '') {
            if (($parts = parse_url($uri)) === false) {
                throw new \InvalidArgumentException(sprintf('Unable to parse URI: %s', $uri));
            }

            $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
            $this->userInfo = isset($parts['user']) ? $this->filterUserInfoPart($parts['user']) : '';
            $this->host = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
            $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
            $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
            $this->query = isset($parts['query']) ? $this->filterQuery($parts['query']) : '';
            $this->fragment = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';

            if (isset($parts['pass'])) {
                $this->userInfo .= sprintf(':%s', $this->filterUserInfoPart($parts['pass']));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return static::createUriString($this->scheme, $this->getAuthority(), $this->path, $this->query, $this->fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $authority = $this->host;

        if ($this->userInfo != '') {
            $authority = sprintf('%s@%s', $this->userInfo, $authority);
        }

        if ($this->port !== null) {
            $authority .= sprintf(':%s', $this->port);
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        if ($this->filterFragment($fragment) === $this->fragment) {
            return $this;
        }

        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        if (($host = $this->filterHost($host)) === $this->host) {
            return $this;
        }

        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        if (($path = $this->filterPath($path)) === $this->path) {
            return $this;
        }

        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        if (($port = $this->filterPort($port)) === $this->port) {
            return $this;
        }

        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        if (($query = $this->filterQuery($query)) === $this->query) {
            return $this;
        }

        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        if (($scheme = $this->filterScheme($scheme)) === $this->scheme) {
            return $this;
        }

        $clone = clone $this;
        $clone->scheme = $scheme;
        $clone->port = $clone->filterPort($clone->port);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $this->filterUserInfoPart($user);

        if ($password !== null) {
            $info .= sprintf(':%s', $this->filterUserInfoPart($password));
        }

        if ($info === $this->userInfo) {
            return $this;
        }

        $clone = clone $this;
        $clone->userInfo = $info;

        return $clone;
    }

    /**
     * Create a uri string.
     *
     * @param string $scheme    the uri scheme
     * @param string $authority the uri authority
     * @param string $path      the uri path
     * @param string $query     the uri query
     * @param string $fragment  the uri fragment
     */
    private static function createUriString(string $scheme, string $authority, string $path, string $query, string $fragment): string
    {
        $uri = '';

        if ($scheme !== '') {
            $uri .= sprintf('%s://', $scheme);
        }

        if ($authority !== '') {
            $uri .= $authority;
        }

        if ($path !== '') {
            // Add a leading slash if necessary.
            if ($uri && substr($path, 0, 1) !== '/') {
                $uri .= '/';
            }

            $uri .= $path;
        }

        if ($query !== '') {
            $uri .= sprintf('?%s', $query);
        }

        if ($fragment !== '') {
            $uri .= sprintf('#%s', $fragment);
        }

        return $uri;
    }

    /**
     * Filters a uri fragment.
     *
     * @param mixed $fragment the uri fragment
     *
     * @throws \InvalidArgumentException on an invalid uri fragment
     */
    private function filterFragment($fragment): string
    {
        if (!is_string($fragment)) {
            throw new \InvalidArgumentException('Uri fragment must be a string.');
        }

        return preg_replace_callback('/(?:[^'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', [__CLASS__, 'urlEncodeChar'], $fragment);
    }

    /**
     * Filters a uri host.
     *
     * @param mixed $host the uri host
     *
     * @throws \InvalidArgumentException on an invalid uri host
     */
    private function filterHost($host): string
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException('Uri host must be a string.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $host = sprintf('[%s]', $host);
        }

        return strtolower($host);
    }

    /**
     * Filters a uri path.
     *
     * @param string $path the uri path
     *
     * @throws \InvalidArgumentException on an invalid uri path
     */
    private function filterPath($path): string
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }

        return preg_replace_callback('/(?:[^'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.':@\/%]+|%(?![A-Fa-f0-9]{2}))/', [__CLASS__, 'urlEncodeChar'], $path);
    }

    /**
     * Filters a uri port.
     *
     * @param null|int $port the uri port
     *
     * @throws \InvalidArgumentException on an invalid uri port
     */
    private function filterPort($port): ?int
    {
        if ($port === null) {
            return $port;
        }

        if (!is_int($port) || ($port < 0 || $port > 65535)) {
            throw new \InvalidArgumentException(sprintf('Invalid port: %d. Must be between 0 and 65535', $port));
        }

        if (!isset(self::SCHEMES[$this->scheme]) || self::SCHEMES[$this->scheme] !== $port) {
            return $port;
        }

        return null;
    }

    /**
     * Filters a uri query string.
     *
     * @param mixed $query the uri query string
     *
     * @throws \InvalidArgumentException on an invalid uri query string
     */
    private function filterQuery($query): string
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException('Uri query must be a string.');
        }

        return preg_replace_callback('/(?:[^'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', [__CLASS__, 'urlEncodeChar'], $query);
    }

    /**
     * Filters a uri scheme.
     *
     * @param mixed $scheme the uri scheme
     *
     * @throws \InvalidArgumentException on an invalid uri scheme
     * @throws \InvalidArgumentException if Uri scheme is not "", "https", or "http"
     */
    private function filterScheme($scheme): string
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('Uri scheme must be a string');
        }

        $scheme = rtrim(strtolower($scheme), ':/');

        if (!isset(self::SCHEMES[$scheme])) {
            throw new \InvalidArgumentException('Uri scheme must be one of: "https", "http"');
        }

        return $scheme;
    }

    /**
     * Filters a uri user-info.
     *
     * @param null|string $component the uri user-info
     *
     * @throws \InvalidArgumentException on an invalid uri user-info
     */
    private function filterUserInfoPart(?string $component): string
    {
        if (!is_string($component)) {
            return '';
        }

        return preg_replace_callback('/(?:[^%'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.']+|%(?![A-Fa-f0-9]{2}))/u', [__CLASS__, 'urlEncodeChar'], $component);
    }

    /**
     * Encodes a uri character.
     *
     * @param array $matches the uri regex matches
     */
    private function urlEncodeChar(array $matches): string
    {
        return rawurlencode($matches[0]);
    }
}
