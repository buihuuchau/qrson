<?php

namespace App\Repositories;

use App\Models\Shipment;
use App\Repositories\BaseRepository;

class ShipmentRepository extends BaseRepository
{
    protected $shipment;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
        $this->setModel();
    }

    public function getModel()
    {
        return Shipment::class;
    }
}
