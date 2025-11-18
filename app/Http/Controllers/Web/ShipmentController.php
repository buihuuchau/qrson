<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
            $shipments = $this->shipmentService->filter($filterShipment);
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

            $filterDocument = [
                'shipment_id' => $result['id'],
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            if ($documents->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đã có số chứng từ liên quan đến Shipment ID này, không thể xóa!',
                ], 400);
            } else {
                $shipment = $this->shipmentService->delete($result['id']);
                if ($shipment != false) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Xóa mã Shipment ID thành công',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Xóa Shipment ID thất bại',
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Xóa mã sản phẩm thất bại',
            ], 400);
        }
    }
}
