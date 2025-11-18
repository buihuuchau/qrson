<?php

namespace App\Services;

use App\Repositories\ShipmentRepository;
use App\Services\BaseService;

class ShipmentService extends BaseService
{

    protected $shipmentRepository;

    public function __construct(ShipmentRepository $shipmentRepository)
    {
        $this->shipmentRepository = $shipmentRepository;
        $this->setRepository();
    }

    public function getRepository()
    {
        return ShipmentRepository::class;
    }
}
