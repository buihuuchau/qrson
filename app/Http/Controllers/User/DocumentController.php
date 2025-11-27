<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\documentAddRequest;
use App\Http\Requests\documentDeleteRequest;
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

    public function scanDocument(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $filterDocument = [
                'shipment_id' => $result['shipment_id'],
                'status' => 'pending',
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
                'orderBy' => 'created_at',
                'get' => [
                    'paginate' => 50,
                ],
            ];
            $documents = $this->documentService->filter($filterDocument);
            $data['documents'] = $documents;
            $data['shipment_id'] = $result['shipment_id'];
            return view('user.scanDocument', $data);
        } catch (\Throwable $th) {
            Log::error('User/DocumentController scan error: ' . $th->getMessage());
            return back()->withErrors('Lỗi hệ thống.');
        }
    }

    public function add(documentAddRequest $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
                'document_id',
                'total',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment = $this->shipmentService->find($result['shipment_id']);
            if (empty($shipment)) {
                return back()->withErrors('Shipment ID không tồn tại. Vui lòng kiểm tra lại!')->withInput();
            }

            $document = $this->documentService->find($result['document_id']);
            if (!empty($document)) {
                return back()->withErrors('Số chứng từ đã tồn tại, không thể tạo mới!')->withInput();
            }

            DB::beginTransaction();
            $valueCreateDocument = [
                'id' => $result['document_id'],
                'shipment_id' => $shipment->id,
                'total_current' => 0,
                'total' => $result['total'],
                'status' => 'pending',
                'created_by' => Auth::user()->name . ' - ' . Auth::user()->phone,
            ];
            $createDocument = $this->documentService->create($valueCreateDocument);

            $valueUpdateShipment = [
                'status' => 'pending',
            ];
            $updateShipment = $this->shipmentService->update($shipment->id, $valueUpdateShipment);
            if ($createDocument && $updateShipment) {
                DB::commit();
                return redirect()->route('user.scan.codeProduct', ['shipment_id' => $shipment->id, 'document_id' => $createDocument->id])->with('success', 'Tạo mới Số chứng từ thành công.');
            } else {
                DB::rollBack();
                return back()->withErrors('Tạo mới Số chứng từ thất bại.')->withInput();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('User/DocumentController add error: ' . $th->getMessage());
            return back()->withErrors('Lỗi hệ thống.');
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
            Log::error('User/DocumentController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
