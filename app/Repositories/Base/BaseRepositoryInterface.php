<?php

namespace App\Repositories\Base;

interface BaseRepositoryInterface
{
    public function buildQuery($filters, $relations, $orderBy, $direction, $columns = ['*']);
    public function query($columns = ['*']);
    public function all(bool $paginate = false, $columns = ['*'], $relations = [], $orderBy = 'created_at', $direction = 'desc',$filters = [], $perPage = 10, $counts =[]);
    public function find($id);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id);

}
