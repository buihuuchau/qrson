<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\codeProductAddRequest;
use App\Http\Requests\codeProductDeleteRequest;
use App\Http\Requests\codeProductScanRequest;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use App\Services\ShipmentService;
use Carbon\Carbon;
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

    public function scanCodeProduct(codeProductScanRequest $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (empty($shipment)) {
                Log::error('User/CodeProductController Shipment No không tồn tại.');
                return redirect()->route('user.scan.shipment');
            }

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                if ($document->shioment_id != $shipment->id) {
                    Log::error('User/CodeProductController Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!');
                    return redirect()->route('user.scan.shipment');
                }
                Log::error('User/CodeProductController Số chứng từ không tồn tại.');
                return redirect()->route('user.scan.shipment');
            }

            $filterCodeProductTemp = [
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
                'orderBy' => 'created_at',
                'get' => [
                    'paginate' => 50,
                ],
            ];
            $codeProducts = $this->codeProductTempService->filter($filterCodeProductTemp);

            $data['codeProducts'] = $codeProducts;
            $data['shipment'] = $shipment;
            $data['document'] = $document;
            return view('user.scanCodeProduct', $data);
        } catch (\Throwable $th) {
            Log::error('User/CodeProductController scan error: ' . $th->getMessage());
            return back()->withErrors('Lỗi hệ thống.');
        }
    }

    public function add(codeProductAddRequest $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'code_product_id',
                'scan'
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $scan = $result['scan'] ?? 'yes';

            $validator = Validator::make($result, (new codeProductAddRequest())->rules(), (new codeProductAddRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (empty($shipment)) {
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 404,
                        'message' => 'Shipment No không tồn tại. Vui lòng kiểm tra lại!',
                    ], 200);
                } else {
                    return back()->withErrors('Shipment No không tồn tại. Vui lòng kiểm tra lại!')->withInput();
                }
            }

            $document = $this->documentService->find($result['document_id']);
            if (empty($document)) {
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 404,
                        'message' => 'Số chứng từ không tồn tại. Vui lòng kiểm tra lại!',
                    ], 200);
                } else {
                    return back()->withErrors('Số chứng từ không tồn tại. Vui lòng kiểm tra lại!')->withInput();
                }
            } elseif ($document->shipment_id != $shipment->id) {
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!',
                    ], 200);
                } else {
                    return back()->withErrors('Số chứng từ không thuộc về Shipment No đã chọn. Vui lòng kiểm tra lại!')->withInput();
                }
            } elseif ($document->status == 'done') {
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số chứng từ đã hoàn tất, không thể thêm mã sản phẩm!',
                    ], 200);
                } else {
                    return back()->withErrors('Số chứng từ đã hoàn tất, không thể thêm mã sản phẩm!')->withInput();
                }
            } elseif ($document->total_current >= $document->total) {
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Số lượng Mã sản phẩm đã đạt đến giới hạn của Số chứng từ này, không thể thêm mã sản phẩm!',
                    ], 200);
                } else {
                    return back()->withErrors('Số lượng Mã sản phẩm đã đạt đến giới hạn của Số chứng từ này, không thể thêm mã sản phẩm!')->withInput();
                }
            }

            $codeProductTemp = $this->codeProductTempService->find($result['code_product_id']);
            $codeProduct = $this->codeProductService->find($result['code_product_id']);
            if (!empty($codeProductTemp) || !empty($codeProduct)) {
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Mã sản phẩm đã tồn tại, không thể tạo mới!',
                    ], 200);
                } else {
                    return back()->withErrors('Mã sản phẩm đã tồn tại, không thể tạo mới!')->withInput();
                }
            }

            DB::beginTransaction();
            $valueCreateCodeProductTemp = [
                'id' => $result['code_product_id'],
                'shipment_id' => $shipment->id,
                'document_id' => $document->id,
                'scan' => $scan,
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
            ];
            $createCodeProductTemp = $this->codeProductTempService->create($valueCreateCodeProductTemp);

            $valueUpdateDocument = [
                'total_current' => $document->total_current + 1,
            ];
            $updateDocument = $this->documentService->update($document->id, $valueUpdateDocument);
            if ($createCodeProductTemp && $updateDocument) {
                $createCodeProductTemp['created_at_format'] = Carbon::parse($createCodeProductTemp->created_at)->format('Y-m-d H:i:s');
                DB::commit();
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => true,
                        'status_code' => 201,
                        'message' => 'Tạo mới Mã sản phẩm thành công.',
                        'data' => [
                            'codeProductTemp' => $createCodeProductTemp,
                            'document' => $updateDocument,
                        ],
                    ], 201);
                } else {
                    return back()->with('success', 'Tạo mới Mã sản phẩm thành công.');
                }
            } else {
                DB::rollBack();
                if ($scan == 'yes') {
                    return response()->json([
                        'status' => false,
                        'status_code' => 409,
                        'message' => 'Tạo mới Mã sản phẩm thất bại.',
                    ], 200);
                } else {
                    return back()->withErrors('Tạo mới Mã sản phẩm thất bại.')->withInput();
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('User/CodeProductController add error: ' . $th->getMessage());
            if ($scan == 'yes') {
                return response()->json([
                    'status' => false,
                    'status_code' => 500,
                    'message' => 'Lỗi hệ thống.',
                ], 500);
            } else {
                return back()->withErrors('Lỗi hệ thống.')->withInput();
            }
        }
    }

    public function delete(Request $request)
    {
        try {
            $acceptFields = [
                'code_product_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $validator = Validator::make($result, (new codeProductDeleteRequest())->rules(), (new codeProductDeleteRequest())->messages());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $codeProductTemp = $this->codeProductTempService->find($result['code_product_id']);
            if (!$codeProductTemp) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Mã sản phẩm không tồn tại',
                ], 200);
            }

            DB::beginTransaction();
            $filterCodeProduct = [
                'id' => $codeProductTemp->id,
                'get' => 'first',
            ];
            $codeProductTemp = $this->codeProductTempService->filter($filterCodeProduct, 'document');

            $valueUpdateDocument = [
                'total_current' => $codeProductTemp->document->total_current - 1,
            ];
            $updateDocument = $this->documentService->update($codeProductTemp->document_id, $valueUpdateDocument);

            $deleteCodeProductTemp = $this->codeProductTempService->delete($codeProductTemp->id);

            if ($updateDocument && $deleteCodeProductTemp) {
                DB::commit();
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Xóa mã sản phẩm thành công',
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Xóa mã sản phẩm thất bại',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('User/CodeProductController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
