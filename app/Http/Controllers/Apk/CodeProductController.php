<?php

namespace App\Http\Controllers\apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\codeProductAddRequest;
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

class CodeProductController extends Controller
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

    public function add(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'code_product_id',
                'scan'
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new codeProductAddRequest())->rules(), (new codeProductAddRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $scan = $result['scan'] ?? 'yes';

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
            } else {
                $totalCurrentDocument = $document->total_current;
                $totalDocument = $document->total;
                if ($document->shipment_id != $shipment->id) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ không thuộc về Shipment ID đã chọn. Vui lòng kiểm tra lại!',
                    ], 409);
                } elseif ($document->status == 'done') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ đã hoàn tất, không thể thêm mã sản phẩm!',
                    ], 409);
                } elseif ($totalCurrentDocument >= $totalDocument) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 403,
                        'message' => 'Số lượng Mã sản phẩm vượt quá giới hạn cho phép của Số chứng từ, không thể thêm mã sản phẩm!',
                    ], 403);
                }
            }

            $codeProductTemp = $this->codeProductTempService->find($result['code_product_id']);
            if (!empty($codeProductTemp)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Mã sản phẩm đã tồn tại, không thể tạo mới!',
                ], 409);
            }

            DB::beginTransaction();
            $createCodeProductTemp = [
                'id' => $result['code_product_id'],
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'scan' => $scan,
                'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
            ];
            $addCodeProductTemp = $this->codeProductTempService->create($createCodeProductTemp);

            $editDocument = true;
            $editShipment = true;
            if ($totalCurrentDocument + 1 < $totalDocument) {
                $updateDocument = [
                    'total_current' => $totalCurrentDocument + 1,
                ];
                $editDocument = $this->documentService->update($document->id, $updateDocument);
            } else {
                $updateDocument = [
                    'total_current' => $totalCurrentDocument + 1,
                    'status' => 'done',
                ];
                $editDocument = $this->documentService->update($document->id, $updateDocument);

                $checkAllDocumentDone = true;
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
                        $updateShipment = [
                            'status' => 'done',
                        ];
                        $editShipment = $this->shipmentService->update($shipment->id, $updateShipment);
                    }
                }
            }
            if ($addCodeProductTemp && $editDocument && $editShipment) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 201,
                    'message' => 'Tạo mới Mã sản phẩm thành công.',
                    'data' => [
                        'codeProductTemp' => $addCodeProductTemp,
                    ],
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Tạo mới Mã sản phẩm thất bại.',
                ], 409);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CodeProductController add error: ' . $th->getMessage());
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
