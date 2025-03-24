<?php

namespace App\Repositories\Base;

class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(public $model){}

    public function all(bool $paginate = false,$columns = ['*'])
    {
       return $paginate ? $this->model->paginate() : $this->model->get($columns);
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
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
}
