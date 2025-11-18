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
}
