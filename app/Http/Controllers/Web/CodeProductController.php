<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CodeProductController extends Controller
{
    protected $documentService;
    protected $codeProductTempService;
    protected $codeProductService;

    public function __construct(
        DocumentService $documentService,
        CodeProductTempService $codeProductTempService,
        CodeProductService $codeProductService
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
                'code_product_id',
                'created_by',
                'from',
                'to',
                'scan',
                'draft'
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            if (!empty($result['shipment_id'])) {
                $filterCodeProduct['shipment_id'] = $result['shipment_id'];
            }

            if (!empty($result['document_id'])) {
                $filterCodeProduct['document_id'] = $result['document_id'];
                $document = $this->documentService->find($filterCodeProduct['document_id']);
                if (!empty($document)) {
                    $data['document'] = $document;
                }
            }

            if (!empty($result['code_product_id'])) {
                $filterCodeProduct['id'] = $result['code_product_id'];
            }

            if (!empty($result['scan'])) {
                $filterCodeProduct['scan'] = $result['scan'];
            }

            $conditions = [];
            if (!empty($result['created_by'])) {
                $conditions[] = '(created_by LIKE "%' . $result['created_by'] . '%")';
            }
            if (!empty($result['from'])) {
                $conditions[] = '(created_at >= "' . $result['from'] . '")';
            }
            if (!empty($result['to'])) {
                $conditions[] = '(created_at <= "' . $result['to'] . '")';
            }
            $sqlWhere = '';
            if (!empty($conditions)) {
                $sqlWhere = implode(' AND ', $conditions);
            }

            $filterCodeProduct['whereRaw'] = $sqlWhere;

            $filterCodeProduct['orderBy'] = [
                [
                    'column' => 'shipment_id',
                    'value' => 'desc',
                ],
                [
                    'column' => 'document_id',
                    'value' => 'desc',
                ],
                [
                    'column' => 'created_at',
                    'value' => 'desc',
                ],
            ];

            $filterCodeProduct['get'] = [
                'paginate' => 50,
            ];
            if (!empty($result['draft']) && $result['draft'] == 1) {
                $codeProducts = $this->codeProductTempService->filter($filterCodeProduct);
            } else {
                $codeProducts = $this->codeProductService->filter($filterCodeProduct);
            }
            $data['codeProducts'] = $codeProducts;
            return view('web.codeProduct.list', $data);
        } catch (\Throwable $th) {
            Log::error('Web/CodeProductController list error: ' . $th->getMessage());
            abort(404);
        }
    }
}
