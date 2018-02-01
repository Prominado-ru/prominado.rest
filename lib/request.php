<?php

namespace Prominado\Rest;

class Request
{
    private $query = [];
    private $http_status = 200;
    private $http_method = 'GET';
    private $headers = [];
    private $server = [];

    public function withStatus($status)
    {
        $this->http_status = $status;
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function getStatusCode()
    {
        return $this->http_status;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    public function setHttpMethod($http_method)
    {
        $this->http_method = $http_method;
    }

    public function getHttpMethod()
    {
        return $this->http_method;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function getQueryList()
    {
        return $this->query;
    }

    public function getQuery($name)
    {
        return $this->query[$name];
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    public function getServer()
    {
        return $this->server;
    }
}