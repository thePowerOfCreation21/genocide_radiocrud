<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

trait ModelHandler
{
    protected mixed $model;

    protected array $fillable = [];

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

    /**
     * @param array $fillable
     * @return $this
     */
    public function setFillable (array $fillable): static
    {
        $this->fillable = $fillable;
        return $this;
    }

    /**
     * @return array
     */
    public function getFillable (): array
    {
        return empty($this->fillable) ? $this->getFillableFromModel() : $this->fillable;
    }

    /**
     * @return array
     */
    protected function getFillableFromModel (): array
    {
        return (new $this->model)->getFillable();
    }

    /**
     * @return array
     */
    protected function getFillableHashMap (): array
    {
        $fillableHashMap = [];
        foreach ($this->getFillable() AS $field)
        {
            $fillableHashMap[$field] = true;
        }
        return $fillableHashMap;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function applyFillableToData (array &$data): array
    {
        $fillableHashMap = $this->getFillableHashMap();
        foreach ($data AS $key => $value)
        {
            if (! isset($fillableHashMap[$key]))
            {
                unset($data[$key]);
            }
        }
        return $data;
    }
}
