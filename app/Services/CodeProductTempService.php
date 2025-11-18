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
}
