<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(
        DocumentService $documentService,
    ) {
        $this->documentService = $documentService;
    }

    public function list(Request $request)
    {
        $acceptFields = [
            'shipment_id',
        ];

        $result = Arr::only(request()->all(), $acceptFields);
        $shipment_id = $result['shipment_id'] ?? null;
        try {
            $filterDocument = [
                '$shipment_id' => $shipment_id,
                'get' => true,
            ];
            $documents = $this->documentService->filter($filterDocument);
            $data['documents'] = $documents;
            return view('web.document.list', $data);
        } catch (\Throwable $th) {
            abort(404);
        }
    }
}
