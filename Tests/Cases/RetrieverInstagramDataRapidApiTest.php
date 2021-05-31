<?php

namespace Prokl\InstagramParserRapidApiBundle\Tests\Cases;

use Exception;
use Mockery;
use Prokl\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;
use Prokl\InstagramParserRapidApiBundle\Services\RetrieverInstagramDataRapidApi;
use Prokl\InstagramParserRapidApiBundle\Services\Transport\InstagramTransportInterface;
use Prokl\TestingTools\Base\BaseTestCase;
use Prokl\TestingTools\Tools\PHPUnitUtils;
use ReflectionException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Class RetrieverInstagramDataRapidApiTest
 * @package Prokl\InstagramParserRapidApiBundle\Tests\Cases
 *
 * @since 31.05.2021
 */
class RetrieverInstagramDataRapidApiTest extends BaseTestCase
{
    /**
     * @var RetrieverInstagramDataRapidApi $obTestObject
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new RetrieverInstagramDataRapidApi(
            $this->getMockCacheItemInterface(['data' => 'OK']),
            $this->getMockCurlDownloader(),
            'instagram_user',
            $_SERVER['DOCUMENT_ROOT']
        );
    }

    /**
     * query().
     *
     * @return void
     * @throws InstagramTransportException
     * @throws InvalidArgumentException
     */
    public function testQuery() : void
    {
        $result = $this->obTestObject->query();

        $this->assertSame(
          $result,
            ['data' => 'OK']
        );
    }

    /**
     * query(). Поведение при ошибках.
     *
     * @return void
     * @throws InstagramTransportException
     * @throws InvalidArgumentException
     */
    public function testQueryErrors() : void
    {
        $this->obTestObject = new RetrieverInstagramDataRapidApi(
            $this->getMockCacheItemInterfaceErrorBehavior(['message' => 'Error message']),
            $this->getMockCurlDownloader(),
            'instagram_user',
            $_SERVER['DOCUMENT_ROOT']
        );

        $this->expectException(InstagramTransportException::class);
        $this->expectExceptionMessage('Error message');

        $this->obTestObject->query();
    }

    /**
     * query(). Ответ не json.
     *
     * @return void
     * @throws InstagramTransportException
     * @throws InvalidArgumentException
     */
    public function testQueryInvalidAnswer() : void
    {
        $this->obTestObject = new RetrieverInstagramDataRapidApi(
            $this->getMockCacheItemInterfaceErrorBehavior(''),
            $this->getMockCurlDownloader(),
            'instagram_user',
            $_SERVER['DOCUMENT_ROOT']
        );

        $this->expectException(InstagramTransportException::class);
        $this->expectExceptionMessage('Get Request Error: answer not json!');

        $this->obTestObject->query();
    }

    /**
     * Callback кэшера. Проверка, что get InstagramTransport вызывается
     * с правильными аргументами.
     *
     * @return void
     * @throws InstagramTransportException
     * @throws InvalidArgumentException
     */
    public function testCaching() : void
    {
        $adapter = new ArrayAdapter();
        $returnValue = ['data' => 'OK'];

        $this->obTestObject = new RetrieverInstagramDataRapidApi(
            $adapter,
            $this->getMockCurlDownloaderRunning(json_encode($returnValue)),
            'instagram_user',
            $_SERVER['DOCUMENT_ROOT']
        );

        $result = $this->obTestObject->query();

        $this->assertSame($returnValue, $result);
    }

    /**
     * Callback кэшера. Ошибки транспорта.
     *
     * @return void
     * @throws InstagramTransportException
     * @throws InvalidArgumentException
     */
    public function testCachingWithError() : void
    {
        $adapter = new ArrayAdapter();
        $returnValue = ['data' => 'OK'];

        $this->obTestObject = new RetrieverInstagramDataRapidApi(
            $adapter,
            $this->getMockCurlDownloaderException(),
            'instagram_user',
            $_SERVER['DOCUMENT_ROOT']
        );

        $this->expectException(InstagramTransportException::class);
        $this->expectExceptionMessage('Get Request Error: answer not json!');

        $this->obTestObject->query();
    }

    /**
     * query(). Проверка на использование моков ответа.
     *
     * @return void
     * @throws InstagramTransportException
     * @throws InvalidArgumentException
     */
    public function testQueryUseMock() : void
    {
        $fixture = $this->getFixtureResponse();
        $this->obTestObject->setUseMock(true, '/Tests/Fixtures/instagram/response.txt');

        $result = $this->obTestObject->query();

        $this->assertSame(
            $result,
            $fixture
        );
    }

    /**
     * getCacheKey().
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetCacheKey() : void
    {
        $instagramUser = 'instagram_user';

        $this->obTestObject->setUserId($instagramUser);
        $result = PHPUnitUtils::callMethod(
            $this->obTestObject,
            'getCacheKey',
            []
        );

        $this->assertSame(
            $result,
            'instagram_parser_rapid_api.parser_cache_key' . $instagramUser,
            'Неверный ключ кэша.'
        );

        $this->obTestObject->setAfterMark('aftermark');
        $result = PHPUnitUtils::callMethod(
            $this->obTestObject,
            'getCacheKey',
            []
        );

        $this->assertSame(
            $result,
            'instagram_parser_rapid_api.parser_cache_key' . $instagramUser . md5('aftermark'),
            'Неверный ключ кэша c параметром aftermark.'
        );
    }

    /**
     * Мок InstagramTransportInterface.
     *
     * @param mixed $return Возвращаемое значение.
     *
     * @return mixed
     */
    private function getMockCurlDownloader($return = '')
    {
        $mock = Mockery::mock(InstagramTransportInterface::class);
        $mock = $mock->shouldReceive('get')->andReturn($return);

        return $mock->getMock();
    }

    /**
     * Мок InstagramTransportInterface. Ожидаемо, что используется.
     *
     * @param mixed $return Возвращаемое значение.
     *
     * @return mixed
     */
    private function getMockCurlDownloaderRunning($return = '')
    {
        $mock = Mockery::mock(InstagramTransportInterface::class);
        $mock = $mock->shouldReceive('get')
            ->withArgs(['/account-medias?userid=instagram_user&first=12'])
            ->once()->andReturn($return);

        return $mock->getMock();
    }

    /**
     * Мок InstagramTransportInterface. Выбрасывает исключение.
     *
     * @return mixed
     */
    private function getMockCurlDownloaderException()
    {
        $mock = Mockery::mock(InstagramTransportInterface::class);
        $mock = $mock->shouldReceive('get')
            ->once()->andThrowExceptions([new Exception]);

        return $mock->getMock();
    }

    /**
     * Мок CacheInterface.
     *
     * @param array $return
     *
     * @return mixed
     */
    private function getMockCacheItemInterface(array $return)
    {
        $mock = Mockery::mock(CacheInterface::class);
        $mock = $mock->shouldReceive('get')->andReturn($return)
                ->shouldReceive('delete')->never()
        ;

        return $mock->getMock();
    }

    /**
     * Мок CacheInterface. Поведение при ошибке.
     *
     * @param mixed $return
     *
     * @return mixed
     */
    private function getMockCacheItemInterfaceErrorBehavior($return)
    {
        $mock = Mockery::mock(CacheInterface::class);
        $mock = $mock->shouldReceive('get')->andReturn($return)
            ->shouldReceive('delete')->once()
        ;

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