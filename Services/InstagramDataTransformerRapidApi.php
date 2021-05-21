<?php

namespace Prokl\InstagramParserRapidApiBundle\Services;

use Prokl\InstagramParserRapidApiBundle\Services\Interfaces\InstagramDataTransformerInterface;
use Prokl\InstagramParserRapidApiBundle\Services\Transport\CurlDownloader;
use RuntimeException;

/**
 * Class InstagramDataTransformerRapidApi
 * @package Prokl\InstagramParserRapidApiBundle\Services
 *
 * @since 21.02.2021
 */
class InstagramDataTransformerRapidApi implements InstagramDataTransformerInterface
{
    /**
     * @var CurlDownloader $curlDownloader
     */
    private $curlDownloader;

    /**
     * @var array $arMedias Результат.
     */
    private $arMedias = [];

    /**
     * @var string $dirSave
     */
    private $dirSave;

    /**
     * InstagramDataTransformerRapidApi constructor.
     *
     * @param CurlDownloader $curlDownloader
     * @param string         $dirSave
     */
    public function __construct(
        CurlDownloader $curlDownloader,
        string $dirSave
    ) {
        $this->curlDownloader = $curlDownloader;
        $this->dirSave = $dirSave;
    }

    /**
     * @inheritDoc
     */
    public function processMedias(array $arDataFeed, int $count = 3): array
    {
        /**
         * @internal $arDataFeed['page_info'] =>
         * ['has_next_page' => true, 'end_cursor' => 'XXXXX']
         */
        $countPicture = 1;
        $data = $arDataFeed['edges'] ?? [];

        if (count($data) === 0) {
            throw new RuntimeException('Ничего не получили из Инстаграма.');
        }

        foreach ($data as $item) {
            $item = $item['node'];

            if ($countPicture > $count || !$item) {
                break;
            }

            if ($item['is_video']) {
                continue;
            }

            $resultPathImage = '';
            if ($item['display_url']) {
                $destinationName = '/' . md5($item['display_url']) . '.jpg';
                $resultPathImage = $this->curlDownloader->download($item['display_url'], $this->dirSave . $destinationName);
            }

            $this->arMedias [] = [
                'link' => $item['shortcode'] ? 'https://www.instagram.com/p/' . $item['shortcode'] : '',
                'image' => $resultPathImage,
                'description' => $item['edge_media_to_caption']['edges'][0]['node']['text'] ?? '',
            ];

            $countPicture++;
        }

        return $this->arMedias;
    }

    /**
     * @inheritDoc
     */
    public function getNextPageCursor(array $arDataFeed) : string
    {
        if (!array_key_exists('page_info', $arDataFeed)) {
            return '';
        }

        return $arDataFeed['page_info']['has_next_page']
            ?
            $arDataFeed['page_info']['end_cursor'] : '';
    }

}
