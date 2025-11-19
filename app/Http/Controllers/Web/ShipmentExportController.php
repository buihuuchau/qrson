<?php

namespace App\Http\Controllers\web;

use App\Exports\ShipmentExport;
use App\Http\Controllers\Controller;
use App\Services\CodeProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ShipmentExportController extends Controller
{
    protected $codeProductService;

    public function __construct(
        CodeProductService $codeProductService,
    ) {
        $this->codeProductService = $codeProductService;
    }

    public function exportShipment(Request $request)
    {
        try {
            $acceptFields = [
                'shipment_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $shipment_id = $result['shipment_id'] ?? null;
            $filterCodeProduct = [
                'shipment_id' => $shipment_id,
                'orderBy' => [
                    [
                        'column' => 'document_id',
                        'value' => 'asc',
                    ],
                    [
                        'column' => 'created_at',
                        'value' => 'desc',
                    ],
                ],
                'get' => true,
            ];
            $codeProducts = $this->codeProductService->filter($filterCodeProduct);

            return Excel::download(new ShipmentExport($codeProducts), 'ShipmentNo_' . $shipment_id . '.xlsx');
        } catch (\Throwable $th) {
            Log::error('ExportShipmentController - exportShipment: ' . $th->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất dữ liệu.');
        }
    }
}
