<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

trait HandleQuery
{
    protected array $query = [];

    /**
     * @param array $query
     * @return $this
     */
    public function setQuery (array $query): static
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery (): array
    {
        return $this->query;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function mergeQueryWith(array $query): static
    {
        $this->query = array_merge($this->getQuery(), $query);
        return $this;
    }
}
