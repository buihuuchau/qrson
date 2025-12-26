<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\addDataRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
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

    public function addData(addDataRequest $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'total',
                'note',
                'codeProducts'
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            DB::beginTransaction();
            $shipmentId = null;
            $documentId = null;
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
                    $message[] = 'Tạo mới Shipment No thất bại.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 501,
                        'message' => $message,
                    ], 200);
                }
            }

            $document = $this->documentService->find($result['document_id']);
            if (!empty($document)) {
                DB::rollBack();
                $message[] = 'Số chứng từ đã tồn tại.';
                $message[] = 'Rollback tất cả.';
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => $message,
                ], 200);
            } else {
                $valueCreateDocument = [
                    'id' => $result['document_id'],
                    'shipment_id' => $shipmentId,
                    'total_current' => 0,
                    'total' => $result['total'],
                    'status' => 'pending',
                    'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
                ];
                $createDocument = $this->documentService->create($valueCreateDocument);

                if ($createDocument) {
                    $documentId = $createDocument->id;
                    $message[] = 'Tạo mới Số chứng từ thành công.';
                } else {
                    DB::rollBack();
                    $message[] = 'Tạo mới Số chứng từ thất bại.';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 502,
                        'message' => $message,
                    ], 200);
                }
            }

            // Xử lý mã sản phẩm dù chuỗi hay array cũng chuyển về Array các chuỗi mã sản phẩm:scan
            if (!is_array($result['codeProducts'] ?? null)) {
                $codeProducts = array_filter(
                    array_map('trim', explode(',', (string) $result['codeProducts'])),
                    fn($p) => $p !== ''
                );
            } else {
                $codeProducts = array_map('trim', $result['codeProducts']);
            }

            // Chuyển từng chuỗi mã sản phẩm:scan thành mảng và key là mã, value là scan
            $items = [];
            foreach ($codeProducts as $value) {
                [$code, $scan] = explode(':', $value);
                $items[$code] = $scan;
            }
            $codes = array_keys($items);

            // Lấy các mã sản phẩm đã tồn tại trong bảng tạm và bảng chính
            $existcodeProductTemp = $this->codeProductTempService->getExistingIds($codes);
            $existcodeProduct = $this->codeProductService->getExistingIds($codes);
            $existAll = array_flip(array_merge($existcodeProductTemp, $existcodeProduct));

            // Lọc những mã sản phẩm chưa tồn tại để thêm mới
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
                $message[] = 'Rollback tất cả.';
                return response()->json([
                    'status'  => true,
                    'status_code'  => 400,
                    'message' => $message,
                ], 200);
            } else {
                if (count($newItems) > $result['total']) {
                    DB::rollBack();
                    $message[] = 'Số lượng Mã sản phẩm vượt quá giới hạn cho phép.';
                    $message[] = 'Rollback tất cả.';
                    return response()->json([
                        'status' => false,
                        'status_code' => 401,
                        'message' => $message,
                    ], 200);
                }
            }

            $percentDone = config('app.percent_done');

            if (count($newItems) < $result['total'] * $percentDone) {
                DB::rollBack();
                $message[] = 'Số lượng Mã sản phẩm không đủ để lưu, cần tối thiểu ' . ceil($result['total'] * $percentDone) . ' mã hợp lệ để có thể lưu tạm.';
                $message[] = 'Rollback tất cả.';
                return response()->json([
                    'status' => false,
                    'status_code' => 402,
                    'message' => $message,
                    'data' => [
                        'list_codeProduct_valid' => $newItems
                    ],
                ], 200);
            } else {
                if (count($newItems) >= $result['total'] * $percentDone && count($newItems) < $result['total']) {
                    if (empty($result['note'])) {
                        DB::rollBack();
                        $message[] = 'Nhập thiếu Mã sản phẩm theo số lượng khai báo mà không có lý do.';
                        $message[] = 'Rollback tất cả.';
                        return response()->json([
                            'status' => false,
                            'status_code' => 403,
                            'message' => $message,
                            'data' => [
                                'list_codeProduct_valid' => $newItems
                            ],
                        ], 200);
                    }
                    $createCodeProductTemp = $this->codeProductTempService->insertBatch($newItems);
                    if ($createCodeProductTemp) {
                        $valueUpdateDocument = [
                            'total_current' => count($newItems),
                            'note' => $result['note'],
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
                        ], 200);
                    } else {
                        DB::rollBack();
                        $message[] = 'Thêm Mã sản phẩm vào Số chứng từ vào bảng tạm thất bại.';
                        $message[] = 'Rollback tất cả.';
                        return response()->json([
                            'status' => false,
                            'status_code' => 503,
                            'message' => $message,
                        ], 200);
                    }
                } else {
                    $createCodeProduct = $this->codeProductService->insertBatch($newItems);
                    if ($createCodeProduct) {
                        $valueUpdateDocument = [
                            'total_current' => count($newItems),
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
                            'status_code' => 202,
                            'message' => $message,
                        ], 200);
                    } else {
                        DB::rollBack();
                        $message[] = 'Thêm Mã sản phẩm vào Số chứng từ vào bảng chính thất bại.';
                        $message[] = 'Rollback tất cả.';
                        return response()->json([
                            'status' => false,
                            'status_code' => 504,
                            'message' => $message,
                        ], 200);
                    }
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
