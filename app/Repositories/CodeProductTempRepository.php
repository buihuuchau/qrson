<?php

namespace App\Repositories;

use App\Models\CodeProductTemp;
use App\Repositories\BaseRepository;

class CodeProductTempRepository extends BaseRepository
{
    protected $codeProductTemp;

    public function __construct(CodeProductTemp $codeProductTemp)
    {
        $this->codeProductTemp = $codeProductTemp;
        $this->setModel();
    }

    public function getModel()
    {
        return CodeProductTemp::class;
    }

    public function getExistingIds(array $ids): array
    {
        return $this->model
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
    }

    public function insertBatch(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }
        return $this->model->insert($rows);
    }
}
