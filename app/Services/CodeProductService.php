<?php

namespace App\Services;

use App\Repositories\CodeProductRepository;
use App\Services\BaseService;

class CodeProductService extends BaseService
{

    protected $codeProductRepository;

    public function __construct(CodeProductRepository $codeProductRepository)
    {
        $this->codeProductRepository = $codeProductRepository;
        $this->setRepository();
    }

    public function getRepository()
    {
        return CodeProductRepository::class;
    }

    public function getExistingIds(array $ids): array
    {
        return $this->codeProductRepository->getExistingIds($ids);
    }

    public function insertBatch(array $rows): bool
    {
        return $this->codeProductRepository->insertBatch($rows);
    }
}
