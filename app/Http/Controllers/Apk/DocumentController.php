<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\shipmentRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    protected $shipmentService;
    protected $documentService;
    protected $codeProductService;
    protected $codeProductTempService;

    public function __construct(
        ShipmentService $shipmentService,
        DocumentService $documentService,
        CodeProductService $codeProductService,
        CodeProductTempService $codeProductTempService
    ) {
        $this->shipmentService = $shipmentService;
        $this->documentService = $documentService;
        $this->codeProductService = $codeProductService;
        $this->codeProductTempService = $codeProductTempService;
    }

    public function scanDocument(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new shipmentRequest())->rules(), (new shipmentRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (empty($shipment)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Shipment No không tồn tại.',
                ], 200);
            }

            $filterDocument = [
                'shipment_id' => $shipment->id,
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
                'orderBy' => 'created_at',
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Lấy dữ liệu Số chứng từ của user đang đăng nhập.',
                'data' => [
                    'shipment' => $shipment,
                    'documents' => $documents,
                ],
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Apk/DocumentController scan error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
