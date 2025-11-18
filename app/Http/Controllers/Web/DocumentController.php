<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    protected $documentService;
    protected $codeProductTempService;

    public function __construct(
        DocumentService $documentService,
        CodeProductTempService $codeProductTempService
    ) {
        $this->documentService = $documentService;
        $this->codeProductTempService = $codeProductTempService;
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
            $checkCodeProductTemp = true;
            $filterCodeProductTemp = [
                'document_id' => $result['id'],
                'get' => true,
            ];
            $codeProductTemps = $this->codeProductTempService->filter($filterCodeProductTemp);
            foreach ($codeProductTemps as $codeProductTemp) {
                $codeProductTemp = $this->codeProductTempService->delete($codeProductTemp->id);
                if (!$codeProductTemp) {
                    $checkCodeProductTemp = false;
                }
            }
            $document = $this->documentService->delete($result['id']);
            if ($checkCodeProductTemp && $document) {
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Xóa Số chứng từ thành công',
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Xóa Số chứng từ thất bại',
                ], 400);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('DocumentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Xóa Số chứng từ thất bại',
            ], 400);
        }
    }
}
