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
                'get' => true,
            ];
            $shipments = $this->shipmentService->filter($filterShipment, 'document');
            $data['shipments'] = $shipments;
            return view('web.shipment.list', $data);
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

            $shipment = $this->shipmentService->find($result['id']);
            if (!$shipment) {
                return response()->json([
                    'status' => 'error',
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
                    'status' => 'error',
                    'message' => 'Đã có Số chứng từ liên quan đến Shipment ID này, không thể xóa!',
                ], 400);
            } else {
                $deleteShipment = $this->shipmentService->delete($shipment->id);
                if ($deleteShipment != false) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Xóa Shipment ID thành công',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Xóa Shipment ID thất bại',
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            Log::error('ShipmentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Xóa Shipment ID thất bại',
            ], 400);
        }
    }
}
