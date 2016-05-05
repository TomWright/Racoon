# Racoon API Framework

## Example Application

Cashback\Engine\Engine.php

    namespace Cashback\Engine;


    class Engine
    {

        protected $racoon;

        public function __construct()
        {
            $this->racoon = new \Racoon\Api\App();
            $this->racoon->setRequestClass('\\Cashback\\Engine\\Request');
            $this->racoon->setAuthenticator(new ApiKeyAuthenticator());
        }


        /**
         * Run cashbach engine.
         */
        public function run()
        {
           $this->racoon->run();

            var_dump($this->racoon->getRequest()->getCurrentApiConnection());
        }

    }

Cashback\Engine\Request.php

    namespace Cashback\Engine;


    class Request extends \Racoon\Api\Request
    {

        /**
         * @var \stdClass
         */
        protected $currentApiConnection;


        /**
         * @return \stdClass
         */
        public function getCurrentApiConnection()
        {
            return $this->currentApiConnection;
        }


        /**
         * @param \stdClass $currentApiConnection
         */
        public function setCurrentApiConnection($currentApiConnection)
        {
            $this->currentApiConnection = $currentApiConnection;
        }

    }

Cashback\Engine\ApiKeyAuthenticator.php

    namespace Cashback\Engine;


    use Racoon\Api\Auth\AuthInterface;
    use Racoon\Api\Exception\AuthenticationException;

    /**
     * Class ApiKeyAuthenticator
     * @package Cashback\Engine
     */
    class ApiKeyAuthenticator implements AuthInterface
    {

        /**
         * @var string
         */
        protected $apiKeyName;


        public function __construct()
        {
            $this->setApiKeyName('api_key');
        }


        /**
         * @param Request $request
         * @return bool
         * @throws AuthenticationException
         */
        public function authenticate(\Racoon\Api\Request $request)
        {
            $data = $request->getFullRequest();

            if (! (is_object($data) && isset($data->{$this->getApiKeyName()}))) {
                throw new AuthenticationException($request, 'API Key not found.');
            }

            $apiKey = $data->{$this->getApiKeyName()};

            $apiModel = new \Cashback\Engine\Models\Api();
            $apiConn = $apiModel->validateKey($apiKey);

            if ($apiConn === null) {
                throw new AuthenticationException($request, 'Invalid API Key.');
            }

            $request->setCurrentApiConnection($apiConn);

            return true;
        }


        /**
         * @return string
         */
        public function getApiKeyName()
        {
            return $this->apiKeyName;
        }


        /**
         * @param string $apiKeyName
         */
        public function setApiKeyName($apiKeyName)
        {
            $this->apiKeyName = $apiKeyName;
        }

    }

Cashback\Engine\Models\Api.php

    namespace Cashback\Engine\Models;


    class Api
    {

        public function validateKey($apiKey)
        {
            $result = null;

            if ($apiKey == 'john') {
                $result = new \stdClass();
                $result->api_key = 'john';
            }

            return $result;
        }

    }

Run the Cashback app

    $cashback = new Cashback\Engine\Engine();
    $cashback->run();