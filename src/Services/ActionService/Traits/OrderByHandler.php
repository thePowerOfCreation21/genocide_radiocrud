<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

trait OrderByHandler
{
    protected array $orderBy = ['id' => 'DESC'];

    /**
     * @param array $orderBy
     * @return $this
     */
    public function setOrderBy (array $orderBy): static
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderBy (): array
    {
        return $this->orderBy;
    }
}
