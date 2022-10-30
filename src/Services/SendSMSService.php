<?php

namespace Genocide\Radiocrud\Services;

use Genocide\Radiocrud\Services\SendHTTPRequestService;

class SendSMSService
{
    private $line_numbers = [];

    public function send ($receptor_numbers, string $message)
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

    public function send_otp ($receptor_numbers, $param1)
    {
        if (is_array($receptor_numbers))
        {
            $receptor_numbers = implode(",", $receptor_numbers);
        }

        return (new SendHTTPRequestService())->set_url("https://api.ghasedak.me/v2/verification/send/simple")
            ->set_headers([
                "apikey:" . $this->get_api_key()
            ])
            ->set_body([
                'receptor' => $receptor_numbers,
                'type' => '1',
                'template' => 'ForgotPassword',
                'param1' => $param1
            ])
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
