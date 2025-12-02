<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\documentAddRequest;
use App\Http\Requests\documentDeleteRequest;
use App\Http\Requests\shipmentRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                    'status' => true,
                    'status_code' => 404,
                    'message' => 'Shipment No không tồn tại.',
                ], 200);
            }

            $filterDocument = [
                'shipment_id' => $shipment->id,
                'status' => 'pending',
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

    public function add(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'total',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new documentAddRequest())->rules(), (new documentAddRequest())->messages());

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
            if (!empty($document)) {
                if ($document->shipment_id != $shipment->id) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!',
                    ], 200);
                }
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã tồn tại!',
                ], 200);
            }

            DB::beginTransaction();
            $valueCreateDocument = [
                'id' => $result['document_id'],
                'shipment_id' => $shipment->id,
                'total_current' => 0,
                'total' => $result['total'],
                'status' => 'pending',
                'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
            ];
            $createDocument = $this->documentService->create($valueCreateDocument);

            $valueUpdateShipment = [
                'status' => 'pending',
            ];
            $updateShipment = $this->shipmentService->update($shipment->id, $valueUpdateShipment);
            if ($createDocument && $updateShipment) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 201,
                    'message' => 'Tạo mới Số chứng từ thành công.',
                    'data' => [
                        'document' => $createDocument,
                    ],
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Tạo mới Số chứng từ thất bại.',
                ], 409);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Apk/DocumentController add error: ' . $th->getMessage());
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
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new documentDeleteRequest())->rules(), (new documentDeleteRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $document = $this->documentService->find($result['document_id']);
            if (!$document) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Số chứng từ không tồn tại',
                ], 200);
            } elseif ($document->created_by != Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone) {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'Số chứng từ này không phải do bạn tạo, không thể xóa!',
                ], 200);
            } elseif ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã hoàn tất, không thể xóa',
                ], 200);
            }

            DB::beginTransaction();
            $checkCodeProductTemp = true;
            $filterCodeProductTemp = [
                'document_id' => $document->id,
                'get' => true,
            ];
            $codeProductTemps = $this->codeProductTempService->filter($filterCodeProductTemp);
            foreach ($codeProductTemps as $codeProductTemp) {
                $deleteCodeProductTemp = $this->codeProductTempService->delete($codeProductTemp->id);
                if (!$deleteCodeProductTemp) {
                    $checkCodeProductTemp = false;
                }
            }

            $deleteDocument = $this->documentService->delete($document->id);

            $checkAllDocumentDone = true;
            $updateShipment = true;
            $filterDocument = [
                'shipment_id' => $deleteDocument->shipment_id,
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            if (!empty($documents) && count($documents) > 0) {
                foreach ($documents as $document) {
                    if ($document->status != 'done') {
                        $checkAllDocumentDone = false;
                    }
                }
                if ($checkAllDocumentDone == true) {
                    $valueUpdateShipment = [
                        'status' => 'done',
                    ];
                    $updateShipment = $this->shipmentService->update($deleteDocument->shipment_id, $valueUpdateShipment);
                }
            }

            if ($checkCodeProductTemp && $deleteDocument && $updateShipment) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Xóa Số chứng từ thành công',
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Xóa Số chứng từ thất bại',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Apk/DocumentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
