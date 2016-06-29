<?php


namespace Racoon\Api\Response\Generate;


use Racoon\Api\Request;

class DetailedResponse implements GeneratorInterface
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $httpResponseCode;

    /**
     * @var bool
     */
    protected $showSchema;

    /**
     * @var bool
     */
    protected $showTimeElapsed;

    /**
     * @var bool
     */
    protected $showReceived;

    public function __construct()
    {
        $this->setShowSchema(true);
        $this->setShowTimeElapsed(true);
        $this->setShowReceived(true);
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function generate()
    {
        $this->httpResponseCode = 200;
        
        $displayException = $this->getRequest()->getDisplayException();
        
        $response = new \stdClass();
        $response->success = (! is_object($displayException));
        $response->message = $this->getRequest()->getResponseMessage();
        if (is_object($displayException)) {
            $response->message = $displayException->getMessage();
            $this->httpResponseCode = $displayException->getCode();
            if ($this->httpResponseCode == 0) {
                $this->httpResponseCode = 500;
            }
        }
        if ($this->shouldShowSchema()) {
            if (is_object($this->getRequest()->getSchema())) {
                $response->schema = $this->getRequest()->getSchema()->getDefinition();
            } else {
                $response->schema = null;
            }
        }
        if ($this->shouldShowReceived()) {
            $response->received = $this->request->getFullRequestData();
        }
        if ($this->shouldShowTimeElapsed()) {
            $response->time_elapsed = number_format($this->request->getElapsedTime(true), 3);
        }
        $response->response = $this->getRequest()->getControllerResponse();

        return $response;
    }


    /**
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }


    /**
     * @return boolean
     */
    public function shouldShowSchema()
    {
        return $this->showSchema;
    }


    /**
     * @param boolean $showSchema
     */
    public function setShowSchema($showSchema)
    {
        $this->showSchema = $showSchema;
    }


    /**
     * @return boolean
     */
    public function shouldShowTimeElapsed()
    {
        return $this->showTimeElapsed;
    }


    /**
     * @param boolean $showTimeElapsed
     */
    public function setShowTimeElapsed($showTimeElapsed)
    {
        $this->showTimeElapsed = $showTimeElapsed;
    }


    /**
     * @return boolean
     */
    public function shouldShowReceived()
    {
        return $this->showReceived;
    }


    /**
     * @param boolean $showReceived
     */
    public function setShowReceived($showReceived)
    {
        $this->showReceived = $showReceived;
    }
}