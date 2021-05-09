<?php

namespace Prokl\InstagramParserRapidApiBundle\Services\Transport;

use Prokl\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;

/**
 * Interface InstagramTransportInterface
 * @package Prokl\InstagramParserRapidApiBundle\Services\Transport
 *
 * @since 23.02.2021
 */
interface InstagramTransportInterface
{
    /**
     * @param string $query Строка запроса. Без схемы и хоста API.
     *
     * @return string
     *
     * @throws InstagramTransportException Ошибки транспорта.
     */
    public function get(string $query) : string;
}
