<?php

namespace Genocide\Radiocrud\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;
use Genocide\Radiocrud\Helpers;

class CustomException extends Exception
{
    protected $http_code = 500;

    protected $moreDetails = null;

    /**
     * @param string $message
     * @param int $code
     * @param int $http_code
     * @param $moreDetails
     * @param Throwable|null $previous
     * @throws CustomException
     */
    public function __construct(string $message = "", int $code = 0, int $http_code = 500, $moreDetails = null, Throwable $previous = null)
    {
        parent::__construct($message, $code);
        $this->http_code = $http_code;
        $this->setMoreDetails($moreDetails);
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->http_code;
    }

    /**
     * @throws CustomException
     * @throws Exception
     */
    public function setMoreDetails ($moreDetails = null)
    {
        if (!is_null($moreDetails))
        {
            $moreDetails = Helpers::convertToArray($moreDetails);

            foreach ($moreDetails AS $key => $value)
            {
                $this->setMoreDetail($key, $value);
            }
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     * @throws CustomException
     */
    public function setMoreDetail (string $key, $value)
    {
        $notAllowedKeys = ['message', 'code'];
        if (in_array($key, $notAllowedKeys))
        {
            throw new CustomException('this key is not allowed', 106, 500);
        }
        $this->moreDetails[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getMoreDetails (): mixed
    {
        return $this->moreDetails;
    }

    /**
     * @return JsonResponse
     */
    public function render (): JsonResponse
    {
        $data = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            // 'trace' => $this->getTrace()
        ];

        if (!empty($this->moreDetails))
        {
            $data = array_merge($data, $this->moreDetails);
        }

        return response()->json($data, $this->getHttpCode());
    }
}
