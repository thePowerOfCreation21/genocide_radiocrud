<?php

namespace Genocide\Radiocrud\Services;

class SendHTTPRequestService
{
    // In memory of my beloved send_api_request class ..::TPOC::..
    // config variables

    protected $port = null;

    protected $url = "";

    protected $body = null;

    protected $method = "GET";

    protected $request_headers = [];

    protected $timeout = 10;


    // variables for saving request response

    protected $response = null;

    protected $http_code = 0;


    // methods for setting up the request

    public function set_port (int $port): SendHTTPRequestService
    {
        $this->port = $port;
        return $this;
    }

    public function get_port ()
    {
        return $this->port;
    }

    public function set_url (string $url): SendHTTPRequestService
    {
        $this->url = $url;
        return $this;
    }

    public function get_url (): string
    {
        return $this->url;
    }

    public function set_body ($body): SendHTTPRequestService
    {
        /*
        if (is_array($body))
        {
            foreach ($body as $key => $value)
            {
                $body[$key] = urlencode($value);
            }
        }
        */

        $this->body = $body;
        return $this;
    }

    public function get_body ()
    {
        return $this->body;
    }

    public function set_method (string $method): SendHTTPRequestService
    {
        $this->method = $method;
        return $this;
    }

    public function get_method (): string
    {
        return $this->method;
    }

    public function set_headers ($headers): SendHTTPRequestService
    {
        if (!is_array($headers))
        {
            $headers = [$headers];
        }
        $this->request_headers = $headers;
        return $this;
    }

    public function get_request_headers (): array
    {
        return $this->request_headers;
    }

    public function set_timeout (int $timeout): SendHTTPRequestService
    {
        $this->timeout = $timeout;
        return $this;
    }


    // now let's send request

    public function send ()
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!empty($this->port))
        {
            curl_setopt($ch, CURLOPT_PORT, $this->port);
        }

        if (!empty($this->request_headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->request_headers);
        }

        if (!empty($this->body))
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->body));
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $this->response = curl_exec($ch);
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $this;
    }


    // methods for getting request response

    public function get_response ()
    {
        return $this->response;
    }

    public function get_http_code ()
    {
        return $this->http_code;
    }
}
