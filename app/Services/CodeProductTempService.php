<?php

namespace App\Services;

use App\Repositories\CodeProductTempRepository;
use App\Services\BaseService;

class CodeProductTempService extends BaseService
{

    protected $codeProductTempRepository;

    public function __construct(CodeProductTempRepository $codeProductTempRepository)
    {
        $this->codeProductTempRepository = $codeProductTempRepository;
        $this->setRepository();
    }

    public function getRepository()
    {
        return CodeProductTempRepository::class;
    }

    public function getExistingIds(array $ids): array
    {
        return $this->codeProductTempRepository->getExistingIds($ids);
    }

    public function insertBatch(array $rows): bool
    {
       return $this->codeProductTempRepository->insertBatch($rows);
    }
}
