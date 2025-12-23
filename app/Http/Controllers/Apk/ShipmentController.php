<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Http\Requests\addDataRequest;
use App\Http\Requests\shipmentRequest;
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
                'status' => 'pending',
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

    public function check(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new shipmentRequest())->rules(), (new shipmentRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $shipment = $this->shipmentService->find($result['shipment_id']);

            if (!empty($shipment)) {
                $filterDocument = [
                    'shipment_id' => $shipment->id,
                    'get' => true,
                ];
                $documents = $this->documentService->filter($filterDocument);
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Shipment No đang tồn tại.',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Shipment No không tồn tại.',
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Apk/ShipmentController check error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
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

            $validator = Validator::make($result, (new shipmentRequest())->rules(), (new shipmentRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $checkShipment = $this->shipmentService->find($result['shipment_id']);
            if (!empty($checkShipment)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Shipment No đã tồn tại, không thể tạo mới!',
                ], 200);
            } else {
                $valueCreateShipment = [
                    'id' => $result['shipment_id'],
                    'status' => 'pending',
                    'created_by' => Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone,
                ];
                $createShipment = $this->shipmentService->create($valueCreateShipment);
                if ($createShipment) {
                    return response()->json([
                        'status' => true,
                        'status_code' => 201,
                        'message' => 'Tạo mới Shipment No thành công.',
                    ], 201);
                } else {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Tạo mới Shipment No thất bại.',
                    ], 200);
                }
            }
        } catch (\Throwable $th) {
            Log::error('Apk/ShipmentController add error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
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

            $validator = Validator::make($result, (new shipmentRequest())->rules(), (new shipmentRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (!$shipment) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Shipment No không tồn tại.',
                ], 200);
            } elseif ($shipment->created_by != Auth::guard('api')->user()->name . ' - ' . Auth::guard('api')->user()->phone) {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'Shipment No này không phải do bạn tạo, không thể xóa!',
                ], 200);
            } elseif ($shipment->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Shipment No đã hoàn thành, không thể xóa!',
                ], 200);
            }

            $filterDocument = [
                'shipment_id' => $shipment->id,
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            if ($documents->count() > 0) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Đã có Số chứng từ liên quan đến Shipment No này, không thể xóa!',
                ], 200);
            } else {
                $deleteShipment = $this->shipmentService->delete($shipment->id);
                if ($deleteShipment) {
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
                    ], 200);
                }
            }
        } catch (\Throwable $th) {
            Log::error('Apk/ShipmentController delete error: ' . $th->getMessage());
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
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (empty($shipment)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'messages' => 'Shipment No không tồn tại. Vui lòng kiểm tra lại!',
                ], 200);
            }

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'messages' => 'Số chứng từ không tồn tại. Vui lòng kiểm tra lại!',
                ], 200);
            } elseif ($document->shipment_id != $shipment->id) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'messages' => 'Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!',
                ], 200);
            } elseif ($document->status == 'done') {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'messages' => 'Số chứng từ đã hoàn tất, không thể xác nhận lưu nữa!',
                ], 200);
            }

            $filterCodeProductTemp = [
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'get' => true,
            ];
            $codeProductTemps = $this->codeProductTempService->filter($filterCodeProductTemp);
            if ($document->total_current != $document->total || count($codeProductTemps) != $document->total) {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'messages' => 'Số lượng Mã sản phẩm không khớp, không thể xác nhận lưu!',
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
            ];
            $updateDocument = $this->documentService->update($document->id, $valueUpdateDocument);

            $checkAllDocumentDone = true;
            $updateShipment = true;
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
                    $valueUpdateShipment = [
                        'status' => 'done',
                    ];
                    $updateShipment = $this->shipmentService->update($shipment->id, $valueUpdateShipment);
                }
            }

            if ($checkCreateCodeProduct && $checkDeleteCodeProductTemp && $updateDocument && $updateShipment) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 201,
                    'messages' => 'Xác nhận đã lưu các Mã sản phẩm vào Số chứng từ thành công.',
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'messages' => 'Không thể lưu các Mã sản phẩm cho Số chứng từ này.',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Apk/ShipmentController confirm error: ' . $th->getMessage());
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
