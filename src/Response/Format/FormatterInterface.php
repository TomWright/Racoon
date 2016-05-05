<?php

namespace Racoon\Api\Response\Format;


use Racoon\Api\Exception\ResponseFormattingException;

interface FormatterInterface
{

    /**
     * @param $response
     * @return string
     * @throws ResponseFormattingException
     */
    public function format($response);


    /**
     * @return null|string
     */
    public function getContentType();

}