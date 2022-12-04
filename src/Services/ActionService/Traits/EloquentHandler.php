<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

use Closure;

trait EloquentHandler
{
    protected mixed $eloquent = null;

    /**
     * @param $eloquent
     * @return $this
     */
    public function setEloquent ($eloquent): static
    {
        $this->eloquent = $eloquent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEloquent (): mixed
    {
        return $this->eloquent;
    }

    /**
     * @param Closure $closure
     * @return $this
     */
    public function applyManualChangeToEloquent (Closure $closure): static
    {
        $closure($this->eloquent);
        return $this;
    }
}
