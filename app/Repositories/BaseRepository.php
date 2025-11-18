<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository
{
    protected $model;

    abstract public function getModel();

    public function setModel()
    {
        $this->model = app()->make(
            $this->getModel()
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getWhereIn($array, $column)
    {
        if ($array) {
            return $this->model->whereIn($column, $array)->get();
        } else {
            return $this->model->all();
        }
    }

    public function getWhereNotIn($array, $column)
    {
        if ($array) {
            return $this->model->whereNotIn($column, $array)->get();
        } else {
            return $this->model->all();
        }
    }

    public function getLike($column, $value)
    {
        try {
            $result = $this->getWhere([[$column, 'LIKE', '%' . $value . '%']]);
            return $result;
        } catch (\Throwable $th) {
            Log::error($th);
            return false;
        }
    }

    public function getWhere($conditions)
    {
        try {
            $result = $this->model->where($conditions)->get();
            return $result;
        } catch (\Throwable $th) {
            Log::error($th);
            return false;
        }
    }

    public function create($array = [])
    {
        DB::beginTransaction();
        try {
            $result = $this->model->create($array);
            DB::commit();
            return $result;
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return false;
        }
    }

    public function update($id, $array = [])
    {
        DB::beginTransaction();
        try {
            $result = $this->model->find($id);
            if ($result) {
                $result->update($array);
                DB::commit();
                return $result;
            }
            DB::rollBack();
            return false;
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return false;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->model->find($id);
            if ($result) {
                $result->delete();
                DB::commit();
                return $result;
            }
            DB::rollBack();
            return false;
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return false;
        }
    }

    public function filter($filters, $with)
    {
        try {

            $model = $this->model;
            $tableName = $model->getTable();
            $get = false;
            //  relationship
            if (!empty($with)) {
                if (is_array($with)) {
                    foreach ($with as $key => $value) {
                        $model = $model->with($value);
                    }
                } else {
                    $model = $model->with($with);
                }
            }

            if (!empty($filters)) {
                foreach ($filters as $key => $value) {
                    switch ($key) {
                        case 'join':
                            foreach ($value as $join) {
                                $model = $model->join($join['table'], $join['table_id'], $join['table_reference_id']);
                            }
                            break;

                        case 'selectRaw':
                            $model = $model->selectRaw($value);
                            break;

                        case 'whereRaw':
                            if ($value !== '') {
                                $model = $model->whereRaw($value);
                            }
                            break;

                        case 'whereIn':
                            if (is_array($value)) {
                                $key = $value['key'];
                                $value = $value['value'];
                                $model = $model->whereIn($key, $value);
                            }
                            break;

                        case 'whereNotIn':
                            if (is_array($value)) {
                                $key = $value['key'];
                                $value = $value['value'];
                                $model = $model->whereNotIn($key, $value);
                            }
                            break;
                        case 'sort':
                            $column = $tableName . '.id';
                            $type = 'asc';
                            if (!empty($value['column'])) {
                                $column = $value['column'];
                            }
                            if (!empty($value['type'])) {
                                $type = $value['type'];
                            }
                            $model = $model->orderBy($column, $type);
                            break;

                        case 'groupBy':
                            if (is_array($value)) {
                                foreach ($value as $key => $item) {
                                    if (!empty($item)) {
                                        $model = $model->groupBy($item);
                                    }
                                }
                            } else if (!empty($value)) {
                                $model = $model->groupBy($value);
                            }
                            break;

                        case 'orderBy':
                            if (is_array($value)) {
                                foreach ($value as $key_value => $value_item) {
                                    $column = $value_item['column'];
                                    $value = $value_item['value'];

                                    $model = $model->orderBy(DB::raw($column), $value);
                                }
                            } else {
                                $model = $model->orderBy($value, 'DESC');
                            }
                            break;

                        case 'orderByRaw':
                            if ($value !== '') {
                                $model = $model->orderByRaw($value);
                            }
                            break;

                        case 'distinct':
                            if ($value === true) {
                                $model = $model->distinct();
                            }
                            break;

                        case 'deleted':
                            if ($value === true) {
                                $model = $model->withTrashed();
                            }
                            break;

                        case 'get':
                            if ($value === true) {
                                $model = $model->get();
                                $get = true;
                            }
                            if ($value === 'first') {
                                $model = $model->first();
                                $get = true;
                            }
                            if ($value === 'toSql') {
                                $model = $model->toSql();
                                $get = true;
                            }
                            if (is_array($value)) {
                                foreach ($value as $key => $item) {
                                    if (strtolower($key) === 'paginate') {
                                        $model = $model->paginate($value['paginate']);
                                        $get = true;
                                        break;
                                    }
                                    if (strtolower($key) === 'limit') {
                                        $model = $model->limit($value['limit'])->get();
                                        $get = true;
                                        break;
                                    }
                                }
                            }
                            break;

                        default:
                            if (is_array($value)) {
                                if (!empty($value['operator']) && isset($value['value'])) {
                                    $operator = $value['operator'];
                                    $value = $value['value'];
                                    if (strtolower($operator) === 'like') {
                                        $model = $model->where($key, $operator, '%' . $value . '%');
                                    } else {
                                        $model = $model->where(function ($query) use ($key, $operator, $value) {
                                            $query->where($key, $operator, $value)
                                                ->orWhereNull($key);
                                        });
                                    }
                                }
                            } elseif (isset($value)) {
                                $operator = '=';
                                $model->where($key, $operator, $value);
                            }
                            break;
                    }
                }
            }

            if ($get === false) {
                return $model->get();
            } else {
                return $model;
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return false;
        }
    }

    public function updateOrCreate($arrayCheck, $arrayAdd)
    {
        DB::beginTransaction();
        try {
            $result = $this->model->updateOrCreate($arrayCheck, $arrayAdd);
            if ($result) {
                DB::commit();
                return $result;
            }
            DB::rollBack();
            return false;
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return false;
        }
    }
}
