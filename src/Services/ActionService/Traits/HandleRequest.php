<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

use Illuminate\Http\Request;

trait HandleRequest
{
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
}
