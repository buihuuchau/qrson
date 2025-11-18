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
}
