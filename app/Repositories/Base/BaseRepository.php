<?php

namespace App\Repositories\Base;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;


class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(public $model){}

    public function buildQuery($filters = [], $relations = [], $orderBy='created_at', $direction='asc', $columns = ['*'])
    {
        return QueryBuilder::for(get_class($this->model))
            ->allowedFilters($filters)
            ->select($columns)
            ->with($relations)
            ->orderBy($orderBy, $direction);
    }
    public function all(bool $paginate = false, $columns = ['*'], $relations = [], $orderBy = 'created_at', $direction = 'desc',$filters = [],$perPage = 10): Collection|LengthAwarePaginator
    {
        $query =  $this->query($columns)->with($relations)->orderBy($orderBy, $direction);
        return $paginate ? $query->paginate($perPage) : $query->get($columns);
    }


    public function find($id,$relations = [])
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete($id)
    {
       return $this->model->findOrFail($id)->delete();
    }

    public function query($columns = ['*'])
    {
        return $this->model->select($columns);
    }
}
