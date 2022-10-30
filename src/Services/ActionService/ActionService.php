<?php

namespace Genocide\Radiocrud\Services\ActionService;

use Genocide\Radiocrud\Exceptions\CustomException;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleEloquent;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleModel;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleOrderBy;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleQuery;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleQueryToEloquentClosure;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleRelations;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleRequest;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleRequestData;
use Genocide\Radiocrud\Services\ActionService\Traits\HandleResource;
use Genocide\Radiocrud\Services\PaginationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class ActionService
{
    use HandleRequestData,
        HandleResource,
        HandleRequest,
        HandleModel,
        HandleQueryToEloquentClosure,
        HandleEloquent,
        HandleQuery,
        HandleOrderBy,
        HandleRelations;

    protected bool $applyResource = true;

    public function __construct()
    {
        $this->queryToEloquentClosures['id'] = function (&$eloquent, $query) {
            // $eloquent = $eloquent->where($eloquent->getTable() . '.id', $query['id']);
            $eloquent = $eloquent->where('id', $query['id']);
        };
    }

    /**
     * @param bool $applyResource
     * @return $this
     */
    public function applyResource (bool $applyResource): static
    {
        $this->applyResource = $applyResource;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEloquent (): mixed
    {
        return is_null($this->eloquent) ? new $this->model() : $this->eloquent;
    }

    /**
     * @return $this
     */
    protected function startEloquentIfIsNull (): static
    {
        if (is_null($this->eloquent))
        {
            $this->eloquent = $this->getEloquent();
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function addRelationsToEloquent (): static
    {
        if (! empty($this->relations))
        {
            $this->startEloquentIfIsNull();

            foreach ($this->relations as $relation)
            {
                $this->eloquent = $this->eloquent->with($relation);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addQueryToEloquent (): static
    {
        if (! empty($this->query))
        {
            $this->startEloquentIfIsNull();

            foreach ($this->query as $key => $value)
            {
                if (isset($this->queryToEloquentClosures[$key]) && is_callable($this->queryToEloquentClosures[$key]))
                {
                    $this->queryToEloquentClosures[$key]($this->eloquent, $this->query, $key);
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function addOrderToEloquent(): static
    {
        if (! empty($this->orderBy))
        {
            $this->startEloquentIfIsNull();

            foreach ($this->orderBy as $key => $value)
            {
                $this->eloquent = $this->eloquent->orderBy($key, $value);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function makeEloquent(): static
    {
        return $this
            ->addRelationsToEloquent()
            ->addQueryToEloquent()
            ->addOrderToEloquent();
    }

    /**
     * @param string|null $userClass
     * @return mixed
     * @throws CustomException
     */
    public function getUserFromRequest(string $userClass = null): mixed
    {
        $user = $this->request->user();

        if (empty($user))
        {
            throw new CustomException("could not get user from request", 100, 500);
        }

        if (is_null($userClass))
        {
            return $user;
        }

        if (!is_a($user, $userClass))
        {
            throw new CustomException("user should be instance of " . $userClass, 101, 500);
        }

        return $user;
    }

    /**
     * @param array $data
     * @param callable|null $storing
     * @return mixed
     */
    protected function store(array $data, callable $storing = null): mixed
    {
        if (is_callable($storing))
        {
            $storing($data);
        }
        return $this->applyResourceToEntity($this->model::create($data));
    }

    /**
     * @param callable|null $storing
     * @return mixed
     * @throws CustomException
     */
    public function storeByRequest (callable $storing = null): mixed
    {
        $data = $this->getDataFromRequest($this->getRequest(), $this->getValidationRule());

        return $this->store($data, $storing);
    }

    /**
     * @return $this
     * @throws CustomException
     */
    public function mergeQueryWithQueryFromRequest (): static
    {
        $this->mergeQueryWith(
            $this->getDataFromRequest($this->getRequest(), $this->getValidationRule(), ['throw_exception' => false])
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getByRequestAndEloquent (): array
    {
        $this->startEloquentIfIsNull();
        return (new PaginationService())
            ->setEloquent($this->eloquent)
            ->setRequest($this->request)
            ->setResource($this->getResource())
            ->paginateByRequest();
    }

    /**
     * @param string $id
     * @return $this
     */
    public function queryEloquentById (string $id): static
    {
        return $this
            ->startEloquentIfIsNull()
            ->applyManualChangeToEloquent(function (&$eloquent) use ($id){
                $this->queryToEloquentClosures['id']($eloquent, ['id' => $id]);
            });
    }

    /**
     * @param string $id
     * @return object
     * @throws CustomException
     */
    public function getById (string $id): object
    {
        return $this->queryEloquentById($id)->getFirstByEloquent();
    }

    /**
     * @param null $eloquent
     * @return object
     * @throws CustomException
     */
    protected function getFirstByEloquent ($eloquent = null): object
    {
        $this->startEloquentIfIsNull();

        if (is_null($eloquent))
        {
            $eloquent = $this->getEloquent();
        }

        $entity = $eloquent->first();

        if (empty($entity))
        {
            throw new CustomException(
                "could not find requested $this->model",
                84,
                404
            );
        }

        return $this->applyResourceToEntity($entity);
    }

    /**
     * @param $entity
     * @return mixed
     */
    public function applyResourceToEntity ($entity): mixed
    {
        $resource = $this->getResource();
        return (! $this->applyResource || is_null($resource)) && ! is_object($entity) ? $entity : new $resource($entity);
    }

    /**
     * @param Collection $collection
     * @return Collection|AnonymousResourceCollection
     */
    protected function applyResourceToCollection (Collection $collection): Collection|AnonymousResourceCollection
    {
        $resource = $this->getResource();
        return ! $this->applyResource || is_null($resource) ? $collection : $resource::collection($collection);
    }

    /**
     * @param callable|null $deleting
     * @return mixed
     */
    public function delete (callable $deleting = null): mixed
    {
        $this->startEloquentIfIsNull();

        if (is_callable($deleting))
        {
            $deleting($this->eloquent);
        }

        return $this->eloquent->delete();
    }

    /**
     * @param string $id
     * @param callable|null $deleting
     * @return mixed
     */
    public function deleteById (string $id, callable $deleting = null): mixed
    {
        return $this
            ->queryEloquentById($id)
            ->delete($deleting);
    }

    /**
     * @param array $updateData
     * @param callable|null $updating
     * @return bool|int
     */
    public function update(array $updateData, callable $updating = null): bool|int
    {
        $this->startEloquentIfIsNull();

        if (is_callable($updating))
        {
            $updating($this->eloquent, $updateData);
        }

        return empty($updateData) ? false : $this->eloquent->update($updateData);
    }

    /**
     * @param callable|null $updating
     * @return bool|int
     * @throws CustomException
     */
    public function updateByRequest(callable $updating = null): bool|int
    {
        return $this->update(
            $this->getDataFromRequest($this->request, $this->getValidationRule()),
            $updating
        );
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        $this->startEloquentIfIsNull();
        return $this->eloquent->exists();
    }

    /**
     * @return Collection|AnonymousResourceCollection
     */
    public function getByEloquent (): Collection|AnonymousResourceCollection
    {
        $this->startEloquentIfIsNull();
        return $this->applyResourceToCollection($this->eloquent->get());
    }
}
