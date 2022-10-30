<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

trait HandleModel
{
    protected mixed $model;

    /**
     * @param $model
     * @return $this
     */
    public function setModel ($model): static
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel (): mixed
    {
        return $this->model;
    }
}
