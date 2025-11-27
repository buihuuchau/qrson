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
            $acceptFields = [
                'shipment_id',
                'created_by',
                'from',
                'to',
                'status',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            if (!empty($result['shipment_id'])) {
                $filterShipment['id'] = $result['shipment_id'];
            }

            if (!empty($result['status'])) {
                $filterShipment['status'] = $result['status'];
            }

            $conditions = [];
            if (!empty($result['created_by'])) {
                $conditions[] = '(created_by LIKE "%' . $result['created_by'] . '%")';
            }
            if (!empty($result['from'])) {
                $conditions[] = '(created_at >= "' . $result['from'] . '")';
            }
            if (!empty($result['to'])) {
                $conditions[] = '(created_at <= "' . $result['to'] . '")';
            }
            $sqlWhere = '';
            if (!empty($conditions)) {
                $sqlWhere = implode(' AND ', $conditions);
            }

            $filterShipment['whereRaw'] = $sqlWhere;

            $filterShipment['orderBy'] = 'created_at';

            $filterShipment['get'] = [
                'paginate' => 50,
            ];
            $shipments = $this->shipmentService->filter($filterShipment, 'document');
            $data['shipments'] = $shipments;
            return view('web.shipment.list', $data);
        } catch (\Throwable $th) {
            Log::error('Web/ShipmentController list error: ' . $th->getMessage());
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
                    'status_code' => 404,
                    'message' => 'Shipment No không tồn tại.',
                ], 404);
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
                    'message' => 'Đã có Số chứng từ liên quan đến Shipment No này, không thể xóa!',
                ], 409);
            } else {
                $deleteShipment = $this->shipmentService->delete($shipment->id);
                if ($deleteShipment != false) {
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => 'Xóa Shipment No thành công.',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Xóa Shipment No thất bại.',
                    ], 409);
                }
            }
        } catch (\Throwable $th) {
            Log::error('Web/ShipmentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
