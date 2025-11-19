<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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
                $documents = $this->documentService->filter([
                    'shipment_id' => $shipment->id,
                    'get' => true,
                ]);
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Shipment ID đã được tạo trước đó',
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

            $checkShipment = $this->shipmentService->find($result['shipment_id']);
            if (!empty($checkShipment)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Shipment ID đã tồn tại, không thể tạo mới!',
                ], 409);
            } else {
                $shipment = $this->shipmentService->create([
                    'id' => $result['shipment_id'],
                    'status' => 'pending',
                ]);
                if ($shipment) {
                    return response()->json([
                        'status' => true,
                        'status_code' => 201,
                        'message' => 'Tạo mới Shipment ID thành công',
                        'data' => [
                            'shipment' => $shipment,
                        ],
                    ], 201);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Tạo mới Shipment ID thất bại',
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

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (!$shipment) {
                return response()->json([
                    'status' => false,
                    'status_code' => 400,
                    'message' => 'Shipment ID không tồn tại',
                ], 400);
            }

            $filterDocument = [
                'shipment_id' => $shipment->id,
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);

            if ($shipment->status == 'done' || $documents->count() > 0) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Đã có Số chứng từ liên quan đến Shipment ID này, không thể xóa!',
                ], 409);
            } else {
                $deleteShipment = $this->shipmentService->delete($shipment->id);
                if ($deleteShipment != false) {
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => 'Xóa Shipment ID thành công',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Xóa Shipment ID thất bại',
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
