<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CodeProductController extends Controller
{
    protected $documentService;
    protected $codeProductTempService;
    protected $codeProductService;

    public function __construct(
        DocumentService $documentService,
        CodeProductTempService $codeProductTempService,
        CodeProductService $codeProductService,
    ) {
        $this->documentService = $documentService;
        $this->codeProductTempService = $codeProductTempService;
        $this->codeProductService = $codeProductService;
    }

    public function list(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment_id = $result['shipment_id'] ?? null;
            $document_id = $result['document_id'] ?? null;
            if ($document_id != null) {
                $document = $this->documentService->find($document_id);
                $data['document'] = $document;
            } else {
                $data['document'] = null;
            }

            $filterCodeProduct = [
                'shipment_id' => $shipment_id,
                'document_id' => $document_id,
                'orderBy' => 'created_at',
                'get' => true,
            ];
            if (!empty($document) && $document->total_current != $document->total) {
                $codeProducts = $this->codeProductTempService->filter($filterCodeProduct, 'user');
            } else {
                $codeProducts = $this->codeProductService->filter($filterCodeProduct, 'user');
            }
            $data['codeProducts'] = $codeProducts;
            $data['shipment_id'] = $shipment_id;
            return view('web.codeProduct.list', $data);
        } catch (\Throwable $th) {
            abort(404);
        }
    }
}
