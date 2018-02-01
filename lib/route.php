<?php

namespace Prominado\Rest;

use Bitrix\Main\Context;
use Bitrix\Main\EventManager;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;

class Route
{
    private $method;
    private $query;
    private $http_method;
    private $methodList;
    private $transport = 'json';
    private $error_code;

    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_GET = 'GET';

    public function __construct()
    {
        $this->methodList = $this->getMethodList();
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function setHttpMethod($http_method)
    {
        $this->http_method = $http_method;
    }

    public function setTransport($transport)
    {
        $this->transport = $transport;
    }

    private function getMethodList()
    {
        $list = [[]];

        $event = EventManager::getInstance();
        foreach ($event->findEventHandlers('prominado.rest', 'onRestMethodBuildDescription') as $methodList) {
            if (\is_array($methodList)) {
                $res = ExecuteModuleEventEx($methodList);

                $list[] = $res;
            }
        }

        return array_merge(...$list);
    }

    public function process()
    {
        $response = [];

        $server = Context::getCurrent()->getServer()->toArray();

        $request = new Request();
        $request->setQuery($this->query);
        $request->setServer($server);

        if (!array_key_exists($this->method, $this->methodList)) {
            $response = ['error' => 'METHOD_NOT_FOUND', 'description' => 'Method ' . $this->method . ' not found'];
            $this->error_code = 404;
        }

        $method = $this->methodList[$this->method];

        if (!$this->error_code && method_exists($method['authenticator'][0], $method['authenticator'][1])) {
            if (!\call_user_func($method['authenticator'], $request)) {
                $response = [
                    'error'       => 'UNAUTHORIZED',
                    'description' => 'Unauthorized'
                ];
                $this->error_code = 401;
            }
        }

        if (!$this->error_code && !method_exists($method['callback'][0], $method['callback'][1])) {
            $response = [
                'error'       => 'METHOD_NOT_IMPLEMENTED',
                'description' => 'Method ' . $this->method . ' has not callback handler'
            ];
            $this->error_code = 501;
        }

        if (\is_array($method['allow_methods']) && \count($method['allow_methods']) > 0) {
            if (!\in_array($this->http_method, $method['allow_methods'], false)) {
                $response = [
                    'error'       => 'METHOD_NOT_ALLOWED',
                    'description' => 'Method ' . $this->method . ' not allowed with ' . $this->http_method
                ];
                $this->error_code = 405;
            }
        }

        if ($this->error_code) {
            \CHTTP::SetStatus($this->error_code);
        } else {
            try {
                $res = \call_user_func($method['callback'], $request);

                $http_status = $request->getStatusCode();
                $headers = $request->getHeaders();

                \CHTTP::SetStatus($http_status);

                foreach ($headers as $name => $value) {
                    header($name . ': ' . $value);
                }

                $response['result'] = $res;
                $response['query'] = $this->query;
            } catch (RestException $exception) {
                \CHTTP::SetStatus(400);
                $response = ['error' => $exception->getErrorCode(), 'description' => $exception->getMessage()];
            }
        }

        if ($this->transport === 'xml') {
            header('Content-Type: text/xml; charset=utf-8');
            $response = Encoding::convertEncoding($response, LANG_CHARSET, 'utf-8');

            return $this->withXml(['response' => $response]);
        }

        header('Content-Type: application/json; charset=utf-8');

        return $this->withJson($response);
    }

    private function withJson($response)
    {
        try {
            $res = Json::encode($response);
        } catch (\Exception $exception) {
            $res = Json::encode(['error' => 'WRONG_ENCODING', 'description' => 'Wrong request encoding']);
        }

        return $res;
    }

    private function withXml($response)
    {
        $res = '';
        foreach ($response as $key => $value) {
            if ($key === (int)$key) {
                $key = 'item';
            }

            $res .= '<' . $key . '>';

            if (\is_array($value)) {
                $res .= $this->withXml($value);
            } else {
                $res .= \CDataXML::xmlspecialchars($value);
            }

            $res .= '</' . $key . '>';
        }

        return $res;
    }

    public static function isTransportAvailable($transport)
    {
        return $transport === 'xml' || $transport === 'json';
    }
}