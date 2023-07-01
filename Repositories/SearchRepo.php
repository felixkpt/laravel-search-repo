<?php


namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

/**
 * The SearchRepo class provides functionality for searching and sorting data using Laravel's Eloquent ORM or Query Builder.
 *
 * Author: Felix (https://github.com/felixkpt)
 * Creation Date: July 1, 2023
 * License: MIT
 */
class SearchRepo
{
    protected $builder;

    protected $addedColumns = [];
    protected $sortable = [];

    /**
     * Create a new instance of SearchRepo.
     *
     * @param mixed $builder The builder instance (EloquentBuilder or QueryBuilder).
     * @param array $searchable The columns to search against.
     * @param array $sortable The sortable columns.
     * @return SearchRepo The SearchRepo instance.
     */
    public static function of($builder, $searchable = [], $sortable = [])
    {
        $self = new self;
        $self->sortable = $sortable;

        $term = request()->q;

        $model = null;
        if (method_exists($builder, 'getModel')) {
            $model = $builder->getModel();
            $searchable = $model->searchable ?? [];
        }

        if (empty($term) && empty($searchable)) {
            return ['data' => []];
        }

        if ($builder instanceof EloquentBuilder) {
            foreach ($searchable as $column) {
                if (Str::contains($column, '.')) {
                    [$relation, $column] = Str::parseCallback($column, 2);

                    $builder->orWhereHas($relation, function (EloquentBuilder $query) use ($column, $term) {
                        $query->where($column, 'like', "%$term%");
                    });
                } else {
                    $builder->orWhere($column, 'like', "%$term%");
                }
            }
        } elseif ($builder instanceof QueryBuilder) {
            foreach ($searchable as $column) {
                if (Str::contains($column, '.')) {
                    [$relation, $column] = Str::parseCallback($column, 2);

                    $builder->orWhere(function (QueryBuilder $query) use ($relation, $column, $term) {
                        $query->orWhere($relation . '.' . $column, 'like', "%$term%");
                    });
                } else {
                    $builder->orWhere($column, 'like', "%$term%");
                }
            }
        }

        if (request()->has('orderBy')) {
            $orderBy = Str::lower(request()->orderBy);

            if ($model && $model->hasColumn($orderBy) || in_array($orderBy, $sortable)) {
                $orderDirection = request()->orderDirection ?? 'asc';
                $builder->orderBy($orderBy, $orderDirection);
            }
        }

        $self->builder = $builder;

        return $self;
    }

    /**
     * Add a custom column to the search results.
     *
     * @param string $column The column name.
     * @param \Closure $callback The callback function to generate the column value.
     * @return $this The SearchRepo instance.
     */
    public function addColumn($column, $callback)
    {
        $this->addedColumns[$column] = $callback;

        return $this;
    }

    /**
     * Paginate the search results.
     *
     * @param int $perPage The number of items per page.
     * @param array $columns The columns to retrieve.
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated results.
     */
    function paginate($perPage, $columns = ['*'])
    {
        $builder = $this->builder;

        $perPage = request()->per_page ?? 10;
        $page = request()->page ?? 1;

        // Handle last page results
        $results = $builder->paginate($perPage, $columns, 'page', $page);
        $currentPage = $results->currentPage();
        $lastPage = $results->lastPage();
        $items = $results->items();

        if ($currentPage > $lastPage && count($items) === 0) {
            $results = $builder->paginate($perPage, $columns, 'page', $lastPage);
        }

        $r = $this->additionalColumns($results);

        $results->setCollection(collect($r));

        $custom = collect(['sortable' => $this->sortable]);

        $results = $custom->merge($results);

        return $results;
    }

    /**
     * Get the search results without pagination.
     *
     * @param array $columns The columns to retrieve.
     * @return array The search results.
     */
    function get($columns = ['*'])
    {
        return ['data' => $this->builder->get($columns), 'sortable' => $this->sortable];
    }

    /**
     * Add additional custom columns to the search results.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $results The paginated results.
     * @return array The search results with additional columns.
     */
    function additionalColumns($results)
    {
        $data = $results->items();

        foreach ($data as $item) {
            foreach ($this->addedColumns as $column => $callback) {
                $item->$column = $callback($item);
            }
        }

        return $data;
    }
}
