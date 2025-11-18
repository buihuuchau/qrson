<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(
        DocumentService $documentService,
    ) {
        $this->documentService = $documentService;
    }

    public function list(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment_id = $result['shipment_id'] ?? null;
            $data['shipment_id'] = $shipment_id;

            $filterDocument = [
                'shipment_id' => $data['shipment_id'],
                'orderBy' => 'created_at',
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            $data['documents'] = $documents;
            return view('web.document.list', $data);
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $acceptFields = [
                'id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            DB::beginTransaction();
            $filterCodeProductTemp = [
                'document_id' => $result['id'],
                'get' => true,
            ];
            $codeProduct = $this->codeProductTempService->deleteByDocumentId($result['id']);
            $document = $this->documentService->delete($result['id']);
            $codeProduct = $this->codeProductTempService->delete($result['id']);
            if ($codeProduct != false) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Xóa mã sản phẩm thành công',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Xóa mã sản phẩm thất bại',
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Xóa mã sản phẩm thất bại',
            ], 400);
        }
    }
}
