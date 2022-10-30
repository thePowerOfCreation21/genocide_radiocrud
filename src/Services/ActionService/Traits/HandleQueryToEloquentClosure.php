<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

trait HandleQueryToEloquentClosure
{
    protected array $queryToEloquentClosures = [];

    /**
     * @param array $queryToEloquentClosures
     * @return $this
     */
    public function setQueryToEloquentClosures (array $queryToEloquentClosures): static
    {
        $this->queryToEloquentClosures = $queryToEloquentClosures;
        return $this;
    }

    /**
     * @return array
     */
    public function getQueryToEloquentClosures (): array
    {
        return $this->queryToEloquentClosures;
    }
}
