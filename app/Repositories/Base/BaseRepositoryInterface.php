<?php

namespace App\Repositories\Base;

interface BaseRepositoryInterface
{
    public function query($columns = ['*']);
    public function all(bool $paginate = false,$columns = ['*'], $relations = []);
    public function find($id);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id);

}
