<?php

namespace App\Repositories;

use App\Models\CodeProduct;
use App\Repositories\BaseRepository;

class CodeProductRepository extends BaseRepository
{
    protected $codeProduct;

    public function __construct(CodeProduct $codeProduct)
    {
        $this->codeProduct = $codeProduct;
        $this->setModel();
    }

    public function getModel()
    {
        return CodeProduct::class;
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
