<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\shipmentConfirmRequest;
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
        CodeProductService $codeProductService,
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
                'get' => [
                    'paginate' => 50,
                ],
            ];
            $shipments = $this->shipmentService->filter($filterShipment);
            $data['shipments'] = $shipments;
            return view('user.scanShipment', $data);
        } catch (\Throwable $th) {
            Log::error('User/ShipmentController scan error: ' . $th->getMessage());
            return back()->withErrors('Lỗi hệ thống.');
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
                    'status' => true,
                    'status_code' => 404,
                    'message' => 'Shipment No không tồn tại.',
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('User/ShipmentController check error: ' . $th->getMessage());
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
                    'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
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
            Log::error('User/ShipmentController add error: ' . $th->getMessage());
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
            } elseif ($shipment->created_by != Auth::user()->name . ' - ' . Auth::user()->phone) {
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
            Log::error('User/ShipmentController delete error: ' . $th->getMessage());
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
                return back()->withErrors('Shipment No không tồn tại. Vui lòng kiểm tra lại!');
            }

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                return back()->withErrors('Số chứng từ không tồn tại. Vui lòng kiểm tra lại!');
            } elseif ($document->shipment_id != $shipment->id) {
                return back()->withErrors('Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!');
            } elseif ($document->status == 'done') {
                return back()->withErrors('Số chứng từ đã hoàn tất, không thể xác nhận lưu nữa!');
            }

            $filterCodeProductTemp = [
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'get' => true,
            ];
            $codeProductTemps = $this->codeProductTempService->filter($filterCodeProductTemp);
            if ($document->total_current != $document->total || count($codeProductTemps) != $document->total) {
                return back()->withErrors('Số lượng Mã sản phẩm không khớp, không thể xác nhận lưu!');
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
                return redirect()->route('user.scan.shipment')->with('success', 'Xác nhận đã lưu các Mã sản phẩm vào Số chứng từ thành công.');
            } else {
                DB::rollBack();
                return back()->withErrors('Không thể lưu các Mã sản phẩm cho Số chứng từ này.');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('User/ShipmentController confirm error: ' . $th->getMessage());
            return back()->withErrors('Lỗi hệ thống.');
        }
    }
}
