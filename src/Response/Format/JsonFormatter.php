<?php

namespace Racoon\Api\Response\Format;


use Racoon\Api\Exception\ResponseFormattingException;

class JsonFormatter implements FormatterInterface
{

    /**
     * @param $response
     * @return string
     * @throws ResponseFormattingException
     */
    public function format($response)
    {
        $formattedResponse = json_encode($response);

        if ($formattedResponse === null) {
            throw new ResponseFormattingException(null, 'Could not JSON encode the response.');
        }

        return $formattedResponse;
    }


    /**
     * @return null|string
     */
    public function getContentType()
    {
        return 'application/json';
    }
}