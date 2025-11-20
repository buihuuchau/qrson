<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\shipmentConfirmRequest;
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

class ShipmentController extends Controller
{
    protected $shipmentService;
    protected $documentService;
    protected $codeProductTempService;
    protected $codeProductService;

    public function __construct(
        ShipmentService $shipmentService,
        DocumentService $documentService,
        CodeProductTempService $codeProductTempService,
        CodeProductService $codeProductService,
    ) {
        $this->shipmentService = $shipmentService;
        $this->documentService = $documentService;
        $this->codeProductTempService = $codeProductTempService;
        $this->codeProductService = $codeProductService;
    }

    public function check(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment = $this->shipmentService->find($result['shipment_id']);

            if (!empty($shipment)) {
                $filterDocument = [
                    'shipment_id' => $shipment->id,
                    'get' => true,
                ];
                $documents = $this->documentService->filter($filterDocument);
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Shipment ID đã được tạo trước đó.',
                    'data' => [
                        'shipment' => $shipment,
                        'documents' => $documents,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Shipment ID không tồn tại. Bạn có muốn tạo mới Shipment ID này không?',
                ], 404);
            }
        } catch (\Throwable $th) {
            Log::error('ShipmentController check error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function add(Request $request)
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

            $checkShipment = $this->shipmentService->find($result['shipment_id']);
            if (!empty($checkShipment)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Shipment ID đã tồn tại, không thể tạo mới!',
                ], 409);
            } else {
                $valueCreateShipment = [
                    'id' => $result['shipment_id'],
                    'status' => 'pending',
                    'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
                ];
                $createShipment = $this->shipmentService->create($valueCreateShipment);
                if ($createShipment) {
                    return response()->json([
                        'status' => true,
                        'status_code' => 201,
                        'message' => 'Tạo mới Shipment ID thành công.',
                        'data' => [
                            'shipment' => $createShipment,
                        ],
                    ], 201);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Tạo mới Shipment ID thất bại.',
                    ], 409);
                }
            }
        } catch (\Throwable $th) {
            Log::error('ShipmentController add error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function delete(Request $request)
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
            if (!$shipment) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Shipment ID không tồn tại.',
                ], 404);
            } elseif ($shipment->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Shipment ID đã hoàn thành, không thể xóa!',
                ], 409);
            }

            $filterDocument = [
                'shipment_id' => $shipment->id,
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            if ($documents->count() > 0) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Đã có Số chứng từ liên quan đến Shipment ID này, không thể xóa!',
                ], 409);
            } else {
                $deleteShipment = $this->shipmentService->delete($shipment->id);
                if ($deleteShipment) {
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => 'Xóa Shipment ID thành công.',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Xóa Shipment ID thất bại.',
                    ], 409);
                }
            }
        } catch (\Throwable $th) {
            Log::error('ShipmentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function confirm(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new shipmentConfirmRequest())->rules(), (new shipmentConfirmRequest())->messages());

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
            if (empty($document)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Số chứng từ không tồn tại. Vui lòng kiểm tra lại!',
                ], 404);
            } elseif ($document->shipment_id != $shipment->id) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ không thuộc về Shipment ID đã chọn. Vui lòng kiểm tra lại!',
                ], 409);
            } elseif ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã hoàn tất, không thể xác nhận lưu nữa!',
                ], 409);
            }

            $filterCodeProductTemp = [
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'get' => true,
            ];
            $codeProductTemps = $this->codeProductTempService->filter($filterCodeProductTemp);
            if ($document->total_current != $document->total || count($codeProductTemps) != $document->total) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số lượng Mã sản phẩm không khớp, chưa thể xác nhận lưu!',
                ], 409);
            }

            DB::beginTransaction();
            $checkCreateCodeProduct = true;
            $checkDeleteCodeProductTemp = true;
            foreach ($codeProductTemps as $key => $codeProductTemp) {
                $valueCreateCodeProduct = [
                    'id' => $codeProductTemp->id,
                    'shipment_id' => $codeProductTemp->shipment_id,
                    'document_id' => $codeProductTemp->document_id,
                    'scan' => $codeProductTemp->scan,
                    'created_by' => $codeProductTemp->created_by,
                    'created_at' => $codeProductTemp->created_at,
                ];
                $createCodeProduct = $this->codeProductService->create($valueCreateCodeProduct);
                if (!$createCodeProduct) {
                    $checkCreateCodeProduct = false;
                }

                $deleteCodeProductTemp = $this->codeProductTempService->delete($codeProductTemp->id);
                if (!$deleteCodeProductTemp) {
                    $checkDeleteCodeProductTemp = false;
                }
            }

            $valueUpdateDocument = [
                'status' => 'done',
            ];
            $updateDocument = $this->documentService->update($document->id, $valueUpdateDocument);

            $checkAllDocumentDone = true;
            $updateShipment = true;
            $filterDocument = [
                'shipment_id' => $shipment->id,
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
                    $updateShipment = $this->shipmentService->update($shipment->id, $valueUpdateShipment);
                }
            }

            if ($checkCreateCodeProduct && $checkDeleteCodeProductTemp && $updateDocument && $updateShipment) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 201,
                    'messages' => 'Xác nhận đã lưu các Mã sản phẩm vào Số chứng từ thành công.',
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => true,
                    'status_code' => 409,
                    'messages' => 'Chưa thể lưu các Mã sản phẩm cho Số chứng từ này.',
                ], 409);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('ShipmentController confirm error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
