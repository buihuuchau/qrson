<?php

namespace App\Repositories;

use App\Models\Document;
use App\Repositories\BaseRepository;

class DocumentRepository extends BaseRepository
{
    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->setModel();
    }

    public function getModel()
    {
        return Document::class;
    }
}
