<?php

namespace Genocide\Radiocrud\Services;

use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class PaginationService
{
    protected string $method = 'skipLimit';

    protected array $validMethods = ['skipLimit', 'pages'];

    protected int $skip = 0;

    protected int $limit = 100;

    protected $eloquent;

    protected $resource = null;

    protected Request $request;

    protected $limitRange = [
        'min' => 0,
        'max' => 100
    ];

    protected $skipRange = [
        'min' => 0,
        'max' => PHP_INT_MAX
    ];

    public function __construct()
    {
        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod (string $method): static
    {
        if (in_array($method, $this->validMethods))
        {
            $this->method = $method;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod (): string
    {
        return $this->method;
    }

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
     * @param $resource
     * @return $this
     */
    public function setResource ($resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return null
     */
    public function getResource ()
    {
        return $this->resource;
    }

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

    /**
     * @param int|null $min
     * @param int|null $max
     * @return $this
     */
    public function setLimitRange (int|null $min = null, int|null $max = null): static
    {
        if (!is_null($min))
            $this->limitRange['min'] = $min;
        if (!is_null($max))
            $this->limitRange['max'] = $max;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getLimitRange (): array
    {
        return $this->limitRange;
    }

    /**
     * @param int|null $min
     * @param int|null $max
     * @return $this
     */
    public function setSkipRange (int|null $min = null, int|null $max = null): static
    {
        if (!is_null($min))
            $this->skipRange['min'] = $min;
        if (!is_null($max))
            $this->skipRange['max'] = $max;
        return $this;
    }

    /**
     * @return array
     */
    public function getSkipRange (): array
    {
        return $this->skipRange;
    }

    /**
     * @param int|null $skip
     * @return $this
     */
    public function setSkip (int|null $skip = null): static
    {
        $skipRange = $this->getSkipRange();
        if (!is_null($skip) && $skipRange['min'] <= $skip && $skipRange['max'] >= $skip)
            $this->skip = $skip;
        return $this;
    }

    /**
     * @return int
     */
    public function getSkip (): int
    {
        return $this->skip;
    }

    /**
     * @param int|null $limit
     * @return $this
     */
    public function setLimit (int|null $limit = null): static
    {
        $limitRange = $this->getLimitRange();
        if (!is_null($limit) && $limitRange['min'] <= $limit && $limitRange['max'] >= $limit)
            $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit (): int
    {
        return $this->limit;
    }

    /**
     * @return array
     */
    public function paginate (): array
    {
        return match ($this->getMethod())
        {
            'skipLimit' => $this->skipLimitPaginate()
        };
    }

    /**
     * @return array
     */
    #[ArrayShape(['count' => "", 'data' => ""])]
    public function skipLimitPaginate (): array
    {
        return [
            'count' => $this
                ->getEloquent()
                ->count(),
            'data' => $this->getSkipLimitData()
        ];
    }

    /**
     * @return mixed
     */
    public function getSkipLimitData (): mixed
    {
        $resource = $this->getResource();
        $collection = $this
            ->getEloquent()
            ->skip($this->getSkip())
            ->limit($this->getLimit())
            ->get();
        return is_null($resource) ? $collection : $resource::collection($collection);
    }

    /**
     * @return array
     */
    public function paginateByRequest (): array
    {
        $limitRange = $this->getLimitRange();
        $skipRange = $this->getSkipRange();

        $data = $this->getRequest()->validate([
            'skip' => ['integer', 'min:' . $limitRange['min'], 'max:' . $limitRange['max']],
            'limit' => ['integer', 'min:' . $skipRange['min'], 'max:' . $skipRange['max']],
        ]);

        return $this
            ->setSkip(@$data['skip'])
            ->setLimit(@$data['limit'])
            ->paginate();
    }
}
