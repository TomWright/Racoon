<?php

namespace Racoon\Api;


use Racoon\Api\Schema\Schema;

abstract class Controller
{

    /**
     * @var Request
     */
    protected $request;


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }


    /**
     * @param Schema $schema
     */
    public function setSchema(Schema $schema)
    {
        $this->getRequest()->setSchema($schema);
    }


    /**
     * @param Schema|null $schema
     * @param bool $setSchema
     * @throws Exception\InvalidArgumentException
     */
    public function validateSchema(Schema $schema = null, $setSchema = true)
    {
        if ($schema === null) {
            $schema = $this->getRequest()->getSchema();
        }

        if (is_object($schema)) {
            if ($setSchema) {
                $this->setSchema($schema);
            }
            $schema->validate($this->getRequest()->getRequestData());
        }
    }

}