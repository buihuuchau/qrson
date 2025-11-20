<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\shipmentRequest;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShipmentController extends Controller
{
    protected $shipmentService;
    protected $documentService;

    public function __construct(
        ShipmentService $shipmentService,
        DocumentService $documentService
    ) {
        $this->shipmentService = $shipmentService;
        $this->documentService = $documentService;
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
                            'shipment' => $addShipment,
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
}
