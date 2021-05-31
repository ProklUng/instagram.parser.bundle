<?php

namespace Prokl\InstagramParserRapidApiBundle\Tests\Cases;

use Mockery;
use Prokl\InstagramParserRapidApiBundle\Services\InstagramDataTransformerRapidApi;
use Prokl\InstagramParserRapidApiBundle\Services\Transport\CurlDownloader;
use Prokl\TestingTools\Base\BaseTestCase;
use Prokl\TestingTools\Tools\PHPUnitUtils;
use ReflectionException;
use RuntimeException;

/**
 * Class InstagramDataTransformerRapidApiTest
 * @package Prokl\InstagramParserRapidApiBundle\Tests\Cases
 *
 * @since 31.05.2021
 */
class InstagramDataTransformerRapidApiTest extends BaseTestCase
{
    /**
     * @var InstagramDataTransformerRapidApi $obTestObject
     */
    protected $obTestObject;

    /**
     * @var string $returnPathImg
     */
    private $returnPathImg = '/downloaded.jpg';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new InstagramDataTransformerRapidApi(
            $this->getMockCurlDownloader($this->returnPathImg),
            '/images/',
            $_SERVER['DOCUMENT_ROOT']
        );
    }

    /**
     * processMedias(). Загруженные картинки.
     *
     * @return void
     */
    public function testProcessMediasLoadedPictures() : void
    {
        $feed = $this->getFixtureResponse();

        $result = $this->obTestObject->processMedias($feed);

        foreach ($result as $item) {
            $this->assertSame($this->returnPathImg, $item['image']);
        }
    }

    /**
     * processMedias(). Обрезка по максимальному числу записей.
     *
     * @return void
     */
    public function testProcessMediasCutting() : void
    {
        $feed = $this->getFixtureResponse();

        $result = $this->obTestObject->processMedias($feed, 124);
        $count = count($result);

        // Максимум - 12 записей.
        $this->assertLessThan(
            12,
            $count
        );
    }

    /**
     * getDestinationFilename(). Проверка на преобразование имен файлов в md5.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testImageFilenameConvertions() : void
    {
        $feed = $this->getFixtureResponse();
        foreach ($feed['edges'] as $feedItem) {
            $result = PHPUnitUtils::callMethod(
                $this->obTestObject,
                'getDestinationFilename',
                [
                    $feedItem['node']['display_url']
                ]
            );

            $result = str_replace(['/', '.jpg'], '', $result);

            $this->assertTrue(
                $this->isValidMd5($result),
                'Имя файла не преобразуется в MD5.'
            );
        }
    }

    /**
     * processMedias(). Обработка структуры.
     *
     * @return void
     */
    public function testProcessMediasStructure() : void
    {
        $feed = $this->getFixtureResponse();

        $result = $this->obTestObject->processMedias($feed);

        $shortCodes = [];
        $description = [];

        foreach ($feed['edges'] as $feedItem) {
            $shortCodes[] =  'https://www.instagram.com/p/' . $feedItem['node']['shortcode'];
            $description[] =  $feedItem['node']['edge_media_to_caption']['edges'][0]['node']['text'];
        }

        foreach ($result as $key => $item) {
            $this->assertSame($this->returnPathImg, $item['image']);

            $shortcode = in_array($item['link'], $shortCodes, true);
            $this->assertTrue(
                $shortcode,
                'Shortcode взято с Луны'
            );

            $descriptions = in_array($item['description'], $description, true);
            $this->assertTrue(
                $descriptions,
                'Description взято с Луны'
            );
        }
    }

    /**
     * processMedias(). Пустые данные.
     *
     * @return void
     */
    public function testProcessMediasEmptyData() : void
    {
        $feed = [];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ничего не получили из Инстаграма.');

        $this->obTestObject->processMedias($feed);
    }

    /**
     * processMedias(). Количество картинок.
     *
     * @param integer $countPictures Количество картинок.
     *
     * @return void
     *
     * @dataProvider dataProviderCount
     */
    public function testProcessMediasCountPictures(int $countPictures) : void
    {
        $feed = $this->getFixtureResponse();

        $result = $this->obTestObject->processMedias(
            $feed,
            $countPictures
        );

        $this->assertCount($countPictures, $result, 'Количество картинок обработалось неправильно.');
    }

    /**
     * Количество картинок.
     *
     * @return array
     */
    public function dataProviderCount() : array
    {
        return [
            [3],
            [2],
            [1]
        ];
    }

    /**
     * Проверка строки на валидность MD5.
     *
     * @param string $md5
     *
     * @return boolean
     */
    private function isValidMd5(string $md5 ='') : bool
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    /**
     * Мок CurlDownloader.
     *
     * @param string $returnPath
     *
     * @return mixed
     */
    private function getMockCurlDownloader(string $returnPath)
    {
        $mock = Mockery::mock(CurlDownloader::class);
        $mock = $mock->shouldReceive('download')->andReturn($returnPath);

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