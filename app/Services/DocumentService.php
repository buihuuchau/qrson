<?php

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Services\BaseService;

class DocumentService extends BaseService
{

    protected $documentRepository;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
        $this->setRepository();
    }

    public function getRepository()
    {
        return DocumentRepository::class;
    }
}
