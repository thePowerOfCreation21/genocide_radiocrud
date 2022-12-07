<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler;

use Genocide\Radiocrud\Exceptions\CustomException;
use Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits\CastsHandler;
use Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits\ValidationRuleHandler;
use Illuminate\Http\Request;

trait RequestHandler
{
    use ValidationRuleHandler, CastsHandler;

    protected array $requestData = [];

    protected array $originalRequestData = [];

    protected Request $request;

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest (Request $request): static
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest (): Request
    {
        return $this->request;
    }

    /**
     * @return $this
     * @throws CustomException
     */
    public function setOriginalRequestDataByRequest (): static
    {
        $this->originalRequestData = $this->getRequest()->validate($this->getValidationRule()['laravel']);
        return $this;
    }

    /**
     * @return $this
     * @throws CustomException
     */
    public function setRequestDataByOriginalRequestData (): static
    {
        $this->requestData = $this->castData($this->originalRequestData, $this->getValidationRule()['casts'] ?? []);
        return $this;
    }

    /**
     * @param bool $forceGetFromRequest
     * @return array
     * @throws CustomException
     */
    public function getDataFromRequest (bool $forceGetFromRequest = false): array
    {
        return $forceGetFromRequest || empty($this->requestData) ?
            $this->setOriginalRequestDataByRequest()->setRequestDataByOriginalRequestData()->requestData :
            $this->requestData;
    }
}