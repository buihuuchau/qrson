<?php

namespace App\Http\Controllers\apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\codeProductScanRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CodeProductController extends Controller
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

    public function scanCodeProduct(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new codeProductScanRequest())->rules(), (new codeProductScanRequest())->messages());

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
                    'message' => 'Shipment No không tồn tại.'
                ], 200);
            }

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                if ($document->shioment_id != $shipment->id) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!'
                    ], 200);
                }
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Số chứng từ không tồn tại.'
                ], 200);
            }

            $filterCodeProductTemp = [
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
                'orderBy' => 'created_at',
                'get' => true,
            ];
            $codeProducts = $this->codeProductTempService->filter($filterCodeProductTemp);
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Lấy dữ liệu Mã sản phẩm của user đang đăng nhập.',
                'data' => [
                    'shipment' => $shipment,
                    'document' => $document,
                    'codeProducts' => $codeProducts,
                ]
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Apk/CodeProductController scan error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
