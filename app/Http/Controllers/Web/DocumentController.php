<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CodeProductService;
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

    public function list(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'created_by',
                'from',
                'to',
                'status',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            if (!empty($result['shipment_id'])) {
                $filterDocument['shipment_id'] = $result['shipment_id'];
            }

            if (!empty($result['document_id'])) {
                $filterDocument['id'] = $result['document_id'];
            }

            if (!empty($result['status'])) {
                $filterDocument['status'] = $result['status'];
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

            $filterDocument['whereRaw'] = $sqlWhere;

            $filterDocument['orderBy'] = [
                [
                    'column' => 'shipment_id',
                    'value' => 'desc',
                ],
                [
                    'column' => 'created_at',
                    'value' => 'desc',
                ],
            ];

            $filterDocument['get'] = [
                'paginate' => 50,
            ];
            $documents = $this->documentService->filter($filterDocument);
            $data['documents'] = $documents;
            return view('web.document.list', $data);
        } catch (\Throwable $th) {
            Log::error('Web/DocumentController list error: ' . $th->getMessage());
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
                    'message' => 'Số chứng từ không tồn tại.',
                ], 200);
            } elseif ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã được khóa, không thể xóa.',
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
                    'message' => 'Xóa Số chứng từ thành công.',
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Xóa Số chứng từ thất bại.',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Web/DocumentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function confirm(Request $request)
    {
        try {
            $acceptFields = [
                'document_id',
                'note',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Số chứng từ không tồn tại. Vui lòng kiểm tra lại!',
                ], 200);
            } else if ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số chứng từ đã được xác nhận lưu. Vui lòng kiểm tra lại!',
                ], 200);
            }

            $filterCodeProductTemp = [
                'shipment_id' => $document->shipment_id,
                'document_id' => $document->id,
                'get' => true,
            ];
            $codeProductTemps = $this->codeProductTempService->filter($filterCodeProductTemp);

            $percentDone = config('app.percent_done');
            $countDone = $document->total * $percentDone;
            if ($document->total_current < $countDone || count($codeProductTemps) < $countDone) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Số lượng Mã sản phẩm chưa đủ, cần tối thiểu ' . ceil($countDone) . ' mã để xác nhận lưu!',
                ], 200);
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
                    'updated_at' => $codeProductTemp->updated_at,
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
                'note' => $result['note'],
            ];
            $updateDocument = $this->documentService->update($document->id, $valueUpdateDocument);

            $checkAllDocumentDone = true;
            $updateShipment = true;
            $filterDocument = [
                'shipment_id' => $document->shipment_id,
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
                    $updateShipment = $this->shipmentService->update($document->shipment_id, $valueUpdateShipment);
                }
            }

            if ($checkCreateCodeProduct && $checkDeleteCodeProductTemp && $updateDocument && $updateShipment) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Xác nhận đã lưu các Mã sản phẩm vào Số chứng từ thành công.',
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Không thể lưu các Mã sản phẩm cho Số chứng từ này.',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Web/DocumentController confirm error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
