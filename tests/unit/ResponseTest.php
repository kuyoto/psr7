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

namespace Kuyoto\Psr7\Unit;

use Kuyoto\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Provides a unit test for Response.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class ResponseTest extends TestCase
{
    /**
     * @var Response the response instance
     */
    private $response;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->response = new Response(200, [], fopen('php://temp', 'wb+'));

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->response = null;

        parent::tearDown();
    }

    /**
     * ResponseTest::testStatusCodeIs200ByDefault().
     */
    public function testStatusCodeIs200ByDefault(): void
    {
        $this->assertSame(200, $this->response->getStatusCode());
    }

    /**
     * ResponseTest::testStatusCodeMutatorReturnsCloneWithChanges().
     */
    public function testStatusCodeMutatorReturnsCloneWithChanges(): void
    {
        $response = $this->response->withStatus(400);

        $this->assertNotSame($this->response, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * ResponseTest::testReasonPhraseDefaultsToStandards().
     */
    public function testReasonPhraseDefaultsToStandards(): void
    {
        $response = $this->response->withStatus(422);

        $this->assertSame('Unprocessable Entity', $response->getReasonPhrase());
    }

    /**
     * ResponseTest::testReasonPhraseDefaultsToStandards().
     *
     * @param mixed $code an invalid resposne status code
     *
     * @dataProvider provideInvalidStatusCodes
     */
    public function testCannotSetInvalidStatusCode($code): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->response->withStatus($code);
    }

    /**
     * ResponseTest::testReasonPhraseDefaultsToStandards().
     */
    public function provideInvalidStatusCodes(): array
    {
        return [
            'true' => [true],
            'false' => [false],
            'array' => [[200]],
            'object' => [(object) ['statusCode' => 200]],
            'too-low' => [99],
            'float' => [400.5],
            'too-high' => [600],
            'null' => [null],
            'string' => ['foo'],
        ];
    }

    /**
     * ResponseTest::testReasonPhraseCanBeEmpty().
     */
    public function testReasonPhraseCanBeEmpty(): void
    {
        $response = $this->response->withStatus(555);

        $this->assertIsString($response->getReasonPhrase());
        $this->assertEmpty($response->getReasonPhrase());
    }
}
