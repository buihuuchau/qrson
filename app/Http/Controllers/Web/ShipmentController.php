<?php

namespace App\Http\Controllers\Web;

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

    public function list()
    {
        try {
            $filterShipment = [
                'orderBy' => 'created_at',
                'get' => [
                    'paginate' => 50,
                ],
            ];
            $shipments = $this->shipmentService->filter($filterShipment, 'document');
            $data['shipments'] = $shipments;
            return view('web.shipment.list', $data);
        } catch (\Throwable $th) {
            Log::error('ShipmentController list error: ' . $th->getMessage());
            abort(404);
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
