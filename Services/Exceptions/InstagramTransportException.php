<?php

namespace Prokl\InstagramParserRapidApiBundle\Services\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class InstagramTransportException
 * @package Prokl\InstagramParserRapidApiBundle\Services\Exceptions
 *
 * @since 22.02.2021
 */
class InstagramTransportException extends Exception implements RequestExceptionInterface
{
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->getCode()}]: {$this->getMessage()}\n";
    }
}