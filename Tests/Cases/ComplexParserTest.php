<?php

namespace Prokl\InstagramParserRapidApiBundle\Tests\Cases;

use Exception;
use Mockery;
use Prokl\InstagramParserRapidApiBundle\Services\ComplexParser;
use Prokl\InstagramParserRapidApiBundle\Services\Interfaces\InstagramDataTransformerInterface;
use Prokl\InstagramParserRapidApiBundle\Services\Interfaces\RetrieverInstagramDataInterface;
use Prokl\TestingTools\Base\BaseTestCase;

/**
 * Class ComplexParserTest
 * @package Prokl\InstagramParserRapidApiBundle\Tests\Cases
 *
 * @since 31.05.2021
 */
class ComplexParserTest extends BaseTestCase
{
    /**
     * @var ComplexParser $obTestObject
     */
    protected $obTestObject;

    /**
     * parse().
     *
     * @return void
     * @throws Exception
     */
    public function testParse() : void
    {
        $this->obTestObject = new ComplexParser(
            $this->getMockRetrieverInstagramDataInterface(),
            $this->getMockInstagramDataTransformerInterface()
        );

        $result = $this->obTestObject->parse();

        $this->assertSame(['OK' => true], $result);
    }

    /**
     * parse(). afterParam
     *
     * @return void
     * @throws Exception
     */
    public function testParseAfterParam() : void
    {
        $this->obTestObject = new ComplexParser(
            $this->getMockRetrieverInstagramDataInterface(true),
            $this->getMockInstagramDataTransformerInterface()
        );
        $this->obTestObject->setAfterParam('afterparam');

        $result = $this->obTestObject->parse();

        $this->assertSame(['OK' => true], $result);
    }

    /**
     * getCurrentAfterParam().
     *
     * @return void
     * @throws Exception
     */
    public function testGetCurrentAfterParam() : void
    {
        $this->obTestObject = new ComplexParser(
            $this->getMockRetrieverInstagramDataInterface(),
            $this->getMockInstagramDataTransformerInterfaceGetNextPageCursor()
        );

        $this->obTestObject->setAfterParam('afterparam');

        $result = $this->obTestObject->getCurrentAfterParam();

        $this->assertSame('OK', $result);
    }

    /**
     * Мок RetrieverInstagramDataInterface.
     *
     * @param boolean $setAfterMark
     *
     * @return mixed
     */
    private function getMockRetrieverInstagramDataInterface(
        bool $setAfterMark = false
    ) {
        $mock = Mockery::mock(RetrieverInstagramDataInterface::class);

        $mock = $mock->shouldReceive('query')->once()->andReturn([]);

        if (!$setAfterMark) {
            $mock = $mock->shouldReceive('setAfterMark')->never();
        } else {
            $mock = $mock->shouldReceive('setAfterMark')->once();
        }

        return $mock->getMock();
    }

    /**
     * Мок RetrieverInstagramDataInterface.
     *
     * @return mixed
     */
    private function getMockInstagramDataTransformerInterfaceGetNextPageCursor() {
        $mock = Mockery::mock(InstagramDataTransformerInterface::class);

        $mock = $mock->shouldReceive('getNextPageCursor')->once()->andReturn('OK');

        return $mock->getMock();
    }

    /**
     * Мок RetrieverInstagramDataInterface.
     *
     * @return mixed
     */
    private function getMockInstagramDataTransformerInterface()
    {
        $mock = Mockery::mock(InstagramDataTransformerInterface::class);

        $mock = $mock->shouldReceive('processMedias')->once()->andReturn(['OK' => true]);

        return $mock->getMock();
    }

    /**
     * Response fixture.
     *
     * @return array
     */
    private function getFixtureResponse() : array
    {
        $feed = json_decode(
            file_get_contents(__DIR__.'/../Fixtures/instagram/response.txt'),
            true
        );

        return (array)$feed;
    }

}