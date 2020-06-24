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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 response implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class Response extends Message implements ResponseInterface
{
    /**
     * @var array a map of standard HTTP status code/reason phrases
     */
    private static $phrases = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * @var string the status message
     */
    private $reasonPhrase = '';

    /**
     * @var int the status code
     */
    private $statusCode = 200;

    /**
     * Constructor.
     *
     * @param int                           $status  the response status code
     * @param array                         $headers the response headers
     * @param null|resource|StreamInterface $body    the response body
     *
     * @throws \InvalidArgumentException on an invalid HTTP status code
     */
    public function __construct(int $status = 200, array $headers = [], $body = null)
    {
        $this->statusCode = $this->filterStatus($status);

        parent::__construct($headers, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        if ($this->reasonPhrase !== '') {
            return $this->reasonPhrase;
        }

        if (isset(static::$phrases[$this->statusCode])) {
            return static::$phrases[$this->statusCode];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $clone = clone $this;
        $clone->statusCode = $clone->filterStatus($code);
        $clone->reasonPhrase = $clone->filterReasonPhrase($reasonPhrase);

        return $clone;
    }

    /**
     * Filters the reason phrase.
     *
     * @param mixed $reasonPhrase the status message
     *
     * @throws \InvalidArgumentException on an invalid status message
     */
    private function filterReasonPhrase($reasonPhrase = ''): string
    {
        if (!is_string($reasonPhrase)) {
            throw new \InvalidArgumentException('Response reason phrase must be a string.');
        }

        return $reasonPhrase;
    }

    /**
     * Filters the HTTP status code.
     *
     * @param int $status the HTTP status code
     *
     * @throws \InvalidArgumentException on an invalid HTTP status code
     */
    private function filterStatus($status): int
    {
        if (!is_int($status) || $status < 100 || $status > 599) {
            throw new \InvalidArgumentException('Invalid HTTP status code.');
        }

        return $status;
    }
}
