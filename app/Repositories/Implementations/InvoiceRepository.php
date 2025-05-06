<?php

namespace App\Repositories\Implementations;

use App\Models\Admin;
use App\Models\Invoice;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $invoice)
    {
        parent::__construct($invoice);
    }
}
