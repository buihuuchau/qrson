<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\documentAddRequest;
use App\Http\Requests\documentDeleteRequest;
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

    public function check(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (empty($shipment)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Shipment ID không tồn tại. Vui lòng kiểm tra lại!',
                ], 404);
            }

            $document = $this->documentService->find($result['document_id']);
            if (!empty($document)) {
                if ($document->shipment_id == $result['shipment_id']) {
                    $filterCodeProduct = [
                        'shipment_id' => $document->shipment_id,
                        'document_id' => $document->id,
                        'get' => true,
                    ];
                    if ($document->status == 'pending') {
                        $codeProduct = $this->codeProductTempService->filter($filterCodeProduct);
                        $message = 'Số chứng từ đã được tạo trước đó.';
                    } else {
                        $codeProduct = $this->codeProductService->filter($filterCodeProduct);
                        $message = 'Số chứng từ đã được quét qr hoàn tất, không cho phép chỉnh sửa';
                    }
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => $message,
                        'data' => [
                            'document' => $document,
                            'codeProducts' => $codeProduct,
                        ],
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ này đã thuộc về Shipment ID: ' . $document->shipment_id . ' . Vui lòng kiểm tra lại!',
                    ], 409);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Số chứng từ không tồn tại. Bạn có muốn tạo mới Số chứng từ này không?',
                ], 404);
            }
        } catch (\Throwable $th) {
            Log::error('DocumentController check error: ' . $th->getMessage());
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
                    'message' => 'Shipment ID không tồn tại. Vui lòng kiểm tra lại!',
                ], 404);
            }

            $document = $this->documentService->find($result['document_id']);
            if (!empty($document)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã tồn tại, không thể tạo mới!',
                ], 409);
            }

            DB::beginTransaction();
            $valueCreateDocument = [
                'id' => $result['document_id'],
                'shipment_id' => $shipment->id,
                'total_current' => 0,
                'total' => $result['total'],
                'status' => 'pending',
                'created_by' => (Auth::guard('api')->user()->name ?? Auth::user()->name) . ' - ' . (Auth::guard('api')->user()->phone ?? Auth::user()->phone),
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
            Log::error('DocumentController add error: ' . $th->getMessage());
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
                ], 404);
            } elseif ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã hoàn tất, không thể xóa',
                ], 409);
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
                ], 409);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('DocumentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
