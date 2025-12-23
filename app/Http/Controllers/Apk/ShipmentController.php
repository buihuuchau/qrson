<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\addDataRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        CodeProductService $codeProductService
    ) {
        $this->shipmentService = $shipmentService;
        $this->documentService = $documentService;
        $this->codeProductTempService = $codeProductTempService;
        $this->codeProductService = $codeProductService;
    }

    public function scanShipment(Request $request)
    {
        try {
            $filterShipment = [
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
                'orderBy' => 'created_at',
                'get' => true,
            ];
            $shipments = $this->shipmentService->filter($filterShipment);
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Lấy dữ liệu Shipment No của user đang đăng nhập.',
                'data' => [
                    'shipments' => $shipments,
                ],
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Apk/ShipmentController scan error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function addData(addDataRequest $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'total',
                'scan',
                'codeProducts'
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            DB::beginTransaction();
            $shipmentId = null;
            $documentId = null;
            $total_current = 0;
            $total = $result['total'];
            $message = [];
            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (!empty($shipment)) {
                $shipmentId = $shipment->id;
            } else {
                $valueCreateShipment = [
                    'id' => $result['shipment_id'],
                    'status' => 'pending',
                    'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
                ];
                $createShipment = $this->shipmentService->create($valueCreateShipment);
                if ($createShipment) {
                    $shipmentId = $createShipment->id;
                    $message[] = 'Tạo mới Shipment No thành công.';
                } else {
                    DB::rollBack();
                    $message[] = 'Tạo mới Shipment No thất bại.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => $message,
                    ], 200);
                }
            }

            $document = $this->documentService->find($result['document_id']);
            if (!empty($document)) {
                if ($document->shipment_id != $shipmentId) {
                    DB::rollBack();
                    $message[] = 'Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => $message,
                    ], 200);
                }
                $documentId = $document->id;
                $total_current = $document->total_current;
                $total = $document->total;
            } else {
                $valueCreateDocument = [
                    'id' => $result['document_id'],
                    'shipment_id' => $shipmentId,
                    'total_current' => 0,
                    'total' => $total,
                    'status' => 'pending',
                    'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
                ];
                $createDocument = $this->documentService->create($valueCreateDocument);

                $valueUpdateShipment = [
                    'status' => 'pending',
                ];
                $updateShipment = $this->shipmentService->update($shipmentId, $valueUpdateShipment);

                if ($createDocument && $updateShipment) {
                    $documentId = $createDocument->id;
                    $message[] = 'Tạo mới Số chứng từ thành công.';
                } else {
                    DB::rollBack();
                    $message[] = 'Tạo mới Số chứng từ thất bại.';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => $message,
                    ], 409);
                }
            }

            if (!is_array($result['codeProducts'] ?? null)) {
                $codeProducts = array_filter(
                    array_map('trim', explode(',', (string) $result['codeProducts'])),
                    fn($p) => $p !== ''
                );
            } else {
                $codeProducts = array_map('trim', $result['codeProducts']);
            }

            $items = [];
            foreach ($codeProducts as $value) {
                [$code, $scan] = explode(':', $value);
                $items[$code] = $scan;
            }
            $codes = array_keys($items);
            $existcodeProductTemp = $this->codeProductTempService->getExistingIds($codes);
            $existcodeProduct = $this->codeProductService->getExistingIds($codes);
            $existAll = array_flip(array_merge($existcodeProductTemp, $existcodeProduct));

            $newItems = [];
            foreach ($items as $code => $scan) {
                if (!isset($existAll[$code])) {
                    $newItems[] = [
                        'id'          => $code,
                        'shipment_id' => $shipmentId,
                        'document_id' => $documentId,
                        'scan'        => $scan,
                        'created_by'  => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            if (empty($newItems)) {
                DB::rollBack();
                $message[] = 'Không có Mã sản phẩm mới để thêm.';
                return response()->json([
                    'status'  => true,
                    'status_code'  => 200,
                    'message' => $message,
                ], 200);
            } else {
                if (count($newItems) + $total_current > $total) {
                    DB::rollBack();
                    $message[] = 'Số lượng Mã sản phẩm vượt quá giới hạn cho phép.';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => $message,
                    ], 200);
                }
            }

            $percentDone = config('app.percent_done');

            if (($total_current + count($newItems)) >= ($total * $percentDone) && ($total_current + count($newItems)) < $total) {
                $createCodeProductTemp = $this->codeProductTempService->insertBatch($newItems);
                if ($createCodeProductTemp) {
                    $valueUpdateDocument = [
                        'total_current' => $total_current + count($newItems),
                        'status' => 'pending',
                    ];
                    $updateDocument = $this->documentService->update($documentId, $valueUpdateDocument);

                    $valueUpdateShipment = [
                        'status' => 'pending',
                    ];
                    $updateShipment = $this->shipmentService->update($shipmentId, $valueUpdateShipment);
                }
                if ($createCodeProductTemp && $updateDocument && $updateShipment) {
                    DB::commit();
                    $message[] = 'Thêm Mã sản phẩm vào Số chứng từ thành công vào bảng tạm.';
                    return response()->json([
                        'status' => true,
                        'status_code' => 201,
                        'message' => $message,
                    ], 201);
                } else {
                    DB::rollBack();
                    $message[] = 'Thêm Mã sản phẩm vào Số chứng từ thất bại.';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => $message,
                    ], 409);
                }
            } else {
                $createCodeProduct = $this->codeProductService->insertBatch($newItems);
                if ($createCodeProduct) {
                    $valueUpdateDocument = [
                        'total_current' => $total_current + count($newItems),
                        'status' => 'done',
                    ];
                    $updateDocument = $this->documentService->update($documentId, $valueUpdateDocument);

                    $checkAllDocumentDone = true;
                    $updateShipment = true;
                    $filterDocument = [
                        'shipment_id' => $shipmentId,
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
                }
                if ($createCodeProduct && $updateDocument && $updateShipment) {
                    DB::commit();
                    $message[] = 'Thêm Mã sản phẩm vào Số chứng từ thành công vào bảng thật.';
                    return response()->json([
                        'status' => true,
                        'status_code' => 201,
                        'message' => $message,
                    ], 201);
                } else {
                    DB::rollBack();
                    $message[] = 'Thêm Mã sản phẩm vào Số chứng từ thất bại.';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => $message,
                    ], 409);
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Apk/ShipmentController addData error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
