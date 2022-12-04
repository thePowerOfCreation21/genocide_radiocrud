<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler;

use Genocide\Radiocrud\Exceptions\CustomException;
use Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits\CastsHandler;
use Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits\RequestHandler AS RequestHandlerTrait;
use Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits\ValidationRuleHandler;

trait RequestHandler
{
    use ValidationRuleHandler, CastsHandler, RequestHandlerTrait;

    protected array $requestData = [];

    protected array $originalRequestData = [];

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