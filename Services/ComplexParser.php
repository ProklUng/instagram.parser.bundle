<?php

namespace Prokl\InstagramParserRapidApiBundle\Services;

use Exception;
use Prokl\InstagramParserRapidApiBundle\Services\Interfaces\InstagramDataTransformerInterface;
use Prokl\InstagramParserRapidApiBundle\Services\Interfaces\RetrieverInstagramDataInterface;

/**
 * Class ComplexParser
 * @package Prokl\InstagramParserRapidApiBundle\Services
 *
 * @since 05.12.2020
 */
class ComplexParser
{
    /**
     * @var RetrieverInstagramDataInterface $parserInstagram Сервис парсинга Инстаграма.
     */
    private $parserInstagram;

    /**
     * @var InstagramDataTransformerInterface $dataTransformer Трансформер данных.
     */
    private $dataTransformer;

    /** @var integer $count Сколько картинок запрашивать. */
    private $count = 12;

    /** @var integer $startOffset Начальная точка (страница) для обработки картинок. */
    private $startOffset = 0;

    /**
     * @var string $afterParam
     */
    private $afterParam;

    /**
     * @var array $rawParsedData Спарсенные сырые данные.
     */
    private $rawParsedData = [];

    /**
     * ComplexParser constructor.
     *
     * @param RetrieverInstagramDataInterface   $parserInstagram Сервис парсинга Инстаграма.
     * @param InstagramDataTransformerInterface $dataTransformer Трансформер данных.
     */
    public function __construct(
        RetrieverInstagramDataInterface $parserInstagram,
        InstagramDataTransformerInterface $dataTransformer
    ) {
        $this->parserInstagram = $parserInstagram;
        $this->dataTransformer = $dataTransformer;
    }

    /**
     * Движуха.
     *
     * @return array
     *
     * @throws Exception Ошибки парсинга.
     */
    public function parse() : array
    {
        if ($this->afterParam) {
            $this->parserInstagram->setAfterMark($this->afterParam);
        }

        $this->rawParsedData = $this->parserInstagram->query();

        if ($this->startOffset !== 0) {
            $this->rawParsedData = array_slice($this->rawParsedData, $this->startOffset, $this->count, true);
        }

        return $this->dataTransformer->processMedias($this->rawParsedData, $this->count);
    }

    /**
     * @param integer $count Сколько картинок запрашивать.
     *
     * @return $this
     */
    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @param integer $count Сколько картинок запрашивать транспорту.
     *
     * @return $this
     *
     * @since 09.12.2020
     */
    public function setQueryCount(int $count): self
    {
        $this->parserInstagram->setCount($count);

        return $this;
    }

    /**
     * @param integer $startOffset Начальная точка (страница) для обработки картинок.
     *
     * @return $this
     */
    public function setStartOffset(int $startOffset): self
    {
        $this->startOffset = $startOffset;

        return $this;
    }

    /**
     * @param string $afterParam Параметр after RapidAPI.
     *
     * @return ComplexParser
     */
    public function setAfterParam(string $afterParam): ComplexParser
    {
        $this->afterParam = $afterParam;

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getCurrentAfterParam(): string
    {
        if (count($this->rawParsedData) === 0) {
            $this->parserInstagram->query();
        }

        return $this->dataTransformer->getNextPageCursor($this->rawParsedData);
    }

    /**
     * @param string $userId ID юзера.
     *
     * @return $this
     */
    public function setIdUser(string $userId) : self
    {
        $this->parserInstagram->setUserId($userId);

        return $this;
    }
}
