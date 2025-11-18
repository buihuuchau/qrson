<?php

namespace App\Services;

abstract class BaseService
{
    protected $repository;

    abstract public function getRepository();

    public function setRepository()
    {
        $this->repository = app()->make(
            $this->getRepository()
        );
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function getWhereIn($array = [], $column = 'id')
    {
        return $this->repository->getWhereIn($array, $column);
    }

    public function getWhereNotIn($array = [], $column = 'id')
    {
        return $this->repository->getWhereNotIn($array, $column);
    }

    public function getLike($column, $value)
    {
        return $this->repository->getLike($column, $value);
    }

    public function getWhere($conditions = [])
    {
        return $this->repository->getWhere($conditions);
    }

    public function create($array = [])
    {
        return $this->repository->create($array);
    }

    public function update($id, $array = [])
    {
        return $this->repository->update($id, $array);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function filter($filters = [], $with = '')
    {
        return $this->repository->filter($filters, $with);
    }

    public function updateOrCreate($arrayCheck, $arrayAdd)
    {
        return $this->repository->updateOrCreate($arrayCheck, $arrayAdd);
    }
}
