<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

trait HandleRelations
{
    protected array $relations = [];

    /**
     * @param array $relations
     * @return $this
     */
    public function setRelations (array $relations): static
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelations (): array
    {
        return $this->relations;
    }
}
