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
}
