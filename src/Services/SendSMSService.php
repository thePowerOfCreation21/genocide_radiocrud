<?php

namespace Genocide\Radiocrud\Services;

class SendSMSService
{
    /**
     * @param $receptor_numbers
     * @param string $message
     * @return SendHTTPRequestService
     */
    public function send ($receptor_numbers, string $message): SendHTTPRequestService
    {
        if (is_array($receptor_numbers))
        {
            $receptor_numbers = implode(",", $receptor_numbers);
        }

        return (new SendHTTPRequestService())->set_url("https://api.ghasedak.me/v2/sms/send/pair")
            ->set_method("POST")
            ->set_headers([
                "apikey:" . $this->get_api_key()
            ])
            ->set_body([
                "message" => $message,
                "receptor" => $receptor_numbers,
                //"linenumber" => implode(",", $this->line_numbers)
            ])
            ->send();
    }

    /**
     * @param string|array $receptorNumbers
     * @param string $template
     * @param string ...$params
     * @return SendHTTPRequestService
     */
    public function sendOTP (string|array $receptorNumbers, string $template, string ...$params): SendHTTPRequestService
    {
        if (is_array($receptorNumbers))
        {
            $receptorNumbers = implode(",", $receptorNumbers);
        }

        $requestBody = [
            'receptor' => $receptorNumbers,
            'type' => '1',
            'template' => $template,
        ];

        foreach ($params AS $index => $param)
        {
            $requestBody['param' . ($index+1)] = preg_replace("/[\s\-_]/", '.', $param);
        }

        return (new SendHTTPRequestService())->set_url("https://api.ghasedak.me/v2/verification/send/simple")
            ->set_headers([
                "apikey:" . $this->get_api_key()
            ])
            ->set_body($requestBody)
            ->send();
    }

    /**
     * @return string
     */
    public function get_api_key (): string
    {
        return env('SMS_API_KEY');
    }
}
