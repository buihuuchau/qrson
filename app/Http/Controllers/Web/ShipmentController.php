<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ShipmentService;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(
        ShipmentService $shipmentService,
    ) {
        $this->shipmentService = $shipmentService;
    }

    public function list()
    {
        try {
            $shipments = $this->shipmentService->getAll();
            $data['shipments'] = $shipments;
            return view('web.shipment.list', $data);
        } catch (\Throwable $th) {
            abort(404);
        }
    }
}
