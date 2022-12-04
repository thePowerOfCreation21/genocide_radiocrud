<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits;

use Illuminate\Http\Resources\Json\JsonResource;

trait ResourceHandler
{
    protected array $resources = [];

    protected string | JsonResource $resource;

    /**
     * @param array $resources
     * @return $this
     */
    public function setResources (array $resources): static
    {
        $this->resources = $resources;
        return $this;
    }

    /**
     * @param string $name
     * @param JsonResource $resource
     * @return $this
     */
    public function addToResources (string $name, JsonResource $resource): static
    {
        $this[$name] = $resource;
        return $this;
    }

    /**
     * @param string|JsonResource $resource
     * @return $this
     */
    public function setResource (string | JsonResource $resource): static
    {
        if (is_subclass_of($resource, JsonResource::class))
        {
            $this->resource = $resource;
        }
        else if (isset($this->resources[$resource]))
        {
            $this->resource = $this->resources[$resource];
        }
        return $this;
    }

    /**
     * @param string|null $resource
     * @return JsonResource|string|null
     */
    public function getResource (string $resource = null): JsonResource | null | string
    {
        if (! is_null($resource) && isset($this->resources[$resource]))
        {
            return $this->resources[$resource];
        }
        if (isset($this->resource))
        {
            return $this->resource;
        }
        return @$this->resources['default'];
    }
}
