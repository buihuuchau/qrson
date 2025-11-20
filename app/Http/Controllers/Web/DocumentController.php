<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    protected $shipmentService;
    protected $documentService;
    protected $codeProductTempService;

    public function __construct(
        ShipmentService $shipmentService,
        DocumentService $documentService,
        CodeProductTempService $codeProductTempService
    ) {
        $this->shipmentService = $shipmentService;
        $this->documentService = $documentService;
        $this->codeProductTempService = $codeProductTempService;
    }

    public function list(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment_id = $result['shipment_id'] ?? null;
            $data['shipment_id'] = $shipment_id;

            $filterDocument = [
                'shipment_id' => $data['shipment_id'],
                'orderBy' => [
                    [
                        'column' => 'shipment_id',
                        'value' => 'desc',
                    ],
                    [
                        'column' => 'created_at',
                        'value' => 'desc',
                    ],
                ],
                'get' => [
                    'paginate' => 50,
                ],
            ];
            $documents = $this->documentService->filter($filterDocument);
            $data['documents'] = $documents;
            return view('web.document.list', $data);
        } catch (\Throwable $th) {
            Log::error('DocumentController list error: ' . $th->getMessage());
            abort(404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $acceptFields = [
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

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
                    'message' => 'Số chứng từ đã được khóa, không thể xóa',
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
                $codeProductTemp = $this->codeProductTempService->delete($codeProductTemp->id);
                if (!$codeProductTemp) {
                    $checkCodeProductTemp = false;
                }
            }

            $deleteDocument = $this->documentService->delete($document->id);

            $checkAllDocumentDone = true;
            $checkUpdateShipment = true;
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
                    $editShipment = [
                        'status' => 'done',
                    ];
                    $updateShipment = $this->shipmentService->update($deleteDocument->shipment_id, $editShipment);
                    if (!$updateShipment) {
                        $checkUpdateShipment = false;
                    }
                }
            }

            if ($checkCodeProductTemp && $deleteDocument && $checkUpdateShipment) {
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
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
