<?php
namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            // 🔥 1. Idempotency check
            $existing = Invoice::where('request_id', $data['request_id'])->first();

            if ($existing) {
                return $existing->load('items');
            }

            // 🔥 2. Create invoice
            $invoice = Invoice::create([
                'request_id' => $data['request_id'],
                'invoice_number' => $data['invoice_number'],
                'client_id' => $data['client_id'] ?? null,
                'booking_id' => $data['booking_id'] ?? null,
                'type' => $data['type'],
                'cycle_number' => $data['cycle_number'] ?? null,
                'total' => $data['total'],
                'profit' => $data['profit'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // 🔥 3. Items
            foreach ($data['items'] as $item) {
                $invoice->items()->create($item);
            }

            return $invoice->load('items');
        });
    }
}