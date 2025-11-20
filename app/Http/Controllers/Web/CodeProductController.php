<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CodeProductService;
use App\Services\CodeProductTempService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CodeProductController extends Controller
{
    protected $documentService;
    protected $codeProductTempService;
    protected $codeProductService;

    public function __construct(
        DocumentService $documentService,
        CodeProductTempService $codeProductTempService,
        CodeProductService $codeProductService,
    ) {
        $this->documentService = $documentService;
        $this->codeProductTempService = $codeProductTempService;
        $this->codeProductService = $codeProductService;
    }

    public function list(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment_id = $result['shipment_id'] ?? null;
            $document_id = $result['document_id'] ?? null;
            if ($document_id != null) {
                $document = $this->documentService->find($document_id);
                $data['document'] = $document;
            } else {
                $data['document'] = null;
            }

            $filterCodeProduct = [
                'shipment_id' => $shipment_id,
                'document_id' => $document_id,
                'orderBy' => [
                    [
                        'column' => 'shipment_id',
                        'value' => 'desc',
                    ],
                    [
                        'column' => 'document_id',
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
            if (!empty($document) && $document->total_current != $document->total) {
                $codeProducts = $this->codeProductTempService->filter($filterCodeProduct);
            } else {
                $codeProducts = $this->codeProductService->filter($filterCodeProduct);
            }
            $data['codeProducts'] = $codeProducts;
            $data['shipment_id'] = $shipment_id;
            return view('web.codeProduct.list', $data);
        } catch (\Throwable $th) {
            Log::error('CodeProductController list error: ' . $th->getMessage());
            abort(404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $acceptFields = [
                'code_product_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $codeProductTemp = $this->codeProductTempService->find($result['code_product_id']);
            if (!$codeProductTemp) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Mã sản phẩm không tồn tại',
                ], 404);
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
                ], 409);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CodeProductController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
