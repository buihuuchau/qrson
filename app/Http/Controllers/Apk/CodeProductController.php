<?php

namespace App\Http\Controllers\apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\codeProductAddRequest;
use App\Http\Requests\codeProductDeleteRequest;
use App\Http\Requests\codeProductScanRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                'status' => false,
                'status_code' => 404,
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

    public function add(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'code_product_id',
                'scan'
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $scan = $result['scan'] ?? 'yes';

            $validator = Validator::make($result, (new codeProductAddRequest())->rules(), (new codeProductAddRequest())->messages());

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
                    'message' => 'Shipment No không tồn tại. Vui lòng kiểm tra lại!',
                ], 200);
            }

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Số chứng từ không tồn tại. Vui lòng kiểm tra lại!',
                ], 200);
            } elseif ($document->shipment_id != $shipment->id) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!',
                ], 200);
            } elseif ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã hoàn tất, không thể thêm mã sản phẩm!',
                ], 200);
            } elseif ($document->total_current >= $document->total) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số lượng Mã sản phẩm đã đạt đến giới hạn của Số chứng từ này, không thể thêm mã sản phẩm!',
                ], 200);
            }

            $codeProductTemp = $this->codeProductTempService->find($result['code_product_id']);
            $codeProduct = $this->codeProductService->find($result['code_product_id']);
            if (!empty($codeProductTemp) || !empty($codeProduct)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Mã sản phẩm đã tồn tại, không thể tạo mới!',
                ], 200);
            }

            DB::beginTransaction();
            $valueCreateCodeProductTemp = [
                'id' => $result['code_product_id'],
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'scan' => $scan,
                'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
            ];
            $createCodeProductTemp = $this->codeProductTempService->create($valueCreateCodeProductTemp);

            $valueUpdateDocument = [
                'total_current' => $document->total_current + 1,
            ];
            $updateDocument = $this->documentService->update($document->id, $valueUpdateDocument);
            if ($createCodeProductTemp && $updateDocument) {
                $createCodeProductTemp['created_at_format'] = Carbon::parse($createCodeProductTemp->created_at)->format('Y-m-d H:i:s');
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 201,
                    'message' => 'Tạo mới Mã sản phẩm thành công.',
                    'data' => [
                        'codeProductTemp' => $createCodeProductTemp,
                        'document' => $updateDocument,
                    ],
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Tạo mới Mã sản phẩm thất bại.',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Apk/CodeProductController add error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $acceptFields = [
                'code_product_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new codeProductDeleteRequest())->rules(), (new codeProductDeleteRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $codeProductTemp = $this->codeProductTempService->find($result['code_product_id']);
            if (!$codeProductTemp) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Mã sản phẩm không tồn tại',
                ], 200);
            }

            DB::beginTransaction();
            $filterCodeProduct = [
                'id' => $codeProductTemp->id,
                'get' => 'first',
            ];
            $codeProductTemp = $this->codeProductTempService->filter($filterCodeProduct, 'document');

            $valueUpdateDocument = [
                'total_current' => $codeProductTemp->document->total_current - 1,
            ];
            $updateDocument = $this->documentService->update($codeProductTemp->document_id, $valueUpdateDocument);

            $deleteCodeProductTemp = $this->codeProductTempService->delete($codeProductTemp->id);

            if ($updateDocument && $deleteCodeProductTemp) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Xóa mã sản phẩm thành công',
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Xóa mã sản phẩm thất bại',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Apk/CodeProductController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
