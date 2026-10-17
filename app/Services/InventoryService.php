<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * خصم الخامات والمنتجات لفاتورة بيع مع منع الـ Deadlock والتحقق الصارم المسبق
     */
    public function deductForInvoice(Invoice $invoice, ?int $userId = null): void
    {
        $invoice->loadMissing(['items.product.recipes.inventoryItem']);

        $rawRequirements = [];
        $directProductRequirements = [];

        foreach ($invoice->items as $item) {
            if ($item->item_type !== 'product' || !$item->product_id) {
                continue;
            }

            $product = $item->product;
            if (!$product) {
                $product = Product::with('recipes.inventoryItem')->find($item->product_id);
            }

            if (!$product) {
                continue;
            }

            $qty = (int) ($item->qty ?: 1);

            if ($product->recipes->isNotEmpty()) {
                // المنتج له وصفة مكونات خام
                foreach ($product->recipes as $recipe) {
                    if (!$recipe->inventory_item_id) {
                        continue;
                    }

                    $needed = round((float) $recipe->quantity_used * $qty, 4);
                    $rawRequirements[$recipe->inventory_item_id] = ($rawRequirements[$recipe->inventory_item_id] ?? 0.0) + $needed;
                }
            } else {
                // منتج مباشر بدون وصفة (جاهز مثل كانز أو شوكولاتة)
                $directProductRequirements[$product->id] = ($directProductRequirements[$product->id] ?? 0) + $qty;
            }
        }

        // 1. معالجة الخامات من جدول inventory_items
        if (!empty($rawRequirements)) {
            // 🛡️ ترتيب المعرفات أبجدياً/رقمياً لمنع Deadlock عند وجود طلبات متزامنة
            ksort($rawRequirements);

            $items = InventoryItem::whereIn('id', array_keys($rawRequirements))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // فحص مسبق صارم للأرصدة قبل أي خصم
            foreach ($rawRequirements as $itemId => $neededQuantity) {
                /** @var InventoryItem $invItem */
                $invItem = $items->get($itemId);

                if (!$invItem) {
                    throw new RuntimeException("المادة الخام المطلوبة غير موجودة في المخزن (ID: {$itemId}).", 422);
                }

                if ((float) $invItem->quantity < $neededQuantity) {
                    throw new RuntimeException("الرصيد غير كافٍ للخامة: [{$invItem->name}]. المطلوب: {$neededQuantity} {$invItem->unit}، المتاح في المخزن: {$invItem->quantity} {$invItem->unit}.", 422);
                }
            }

            // تنفيذ الخصم وتسجيل الحركات التاريخية
            foreach ($rawRequirements as $itemId => $neededQuantity) {
                $invItem = $items->get($itemId);
                $newBalance = round((float) $invItem->quantity - $neededQuantity, 2);

                $invItem->update(['quantity' => $newBalance]);

                InventoryMovement::create([
                    'inventory_item_id' => $invItem->id,
                    'type'              => 'sale',
                    'quantity'          => -$neededQuantity,
                    'balance_after'     => $newBalance,
                    'invoice_id'        => $invoice->id,
                    'user_id'           => $userId ?? Auth::id() ?? $invoice->created_by ?? 1,
                ]);
            }
        }

        // 2. معالجة المنتجات المباشرة (التي ليس لها وصفة)
        if (!empty($directProductRequirements)) {
            ksort($directProductRequirements);

            $products = Product::whereIn('id', array_keys($directProductRequirements))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($directProductRequirements as $productId => $neededQty) {
                $prod = $products->get($productId);

                if ($prod && (int) $prod->quantity < $neededQty) {
                    throw new RuntimeException("الكمية غير كافية من المنتج: [{$prod->name}]. المطلوب: {$neededQty}، المتاح: {$prod->quantity}.", 422);
                }
            }

            foreach ($directProductRequirements as $productId => $neededQty) {
                $prod = $products->get($productId);
                if ($prod) {
                    $prod->decrement('quantity', $neededQty);
                }
            }
        }
    }

    /**
     * استرجاع الخامات والمنتجات للمخزن عند إلغاء أو استرجاع الفاتورة (Refund)
     */
    public function restoreForInvoice(Invoice $invoice, string $type = 'sale_cancel', ?int $userId = null): void
    {
        $invoice->loadMissing(['items.product.recipes.inventoryItem']);

        foreach ($invoice->items as $item) {
            if ($item->item_type !== 'product' || !$item->product_id) {
                continue;
            }

            $product = $item->product;
            if (!$product) {
                $product = Product::with('recipes.inventoryItem')->find($item->product_id);
            }

            if (!$product) {
                continue;
            }

            $qty = (int) ($item->qty ?: 1);

            if ($product->recipes->isNotEmpty()) {
                // إرجاع خامات الوصفة
                foreach ($product->recipes as $recipe) {
                    if (!$recipe->inventoryItem) {
                        continue;
                    }

                    $returnedQty = round((float) $recipe->quantity_used * $qty, 4);
                    $recipe->inventoryItem->increment('quantity', $returnedQty);

                    $freshBalance = $recipe->inventoryItem->fresh()->quantity;

                    InventoryMovement::create([
                        'inventory_item_id' => $recipe->inventoryItem->id,
                        'type'              => $type, // 'sale_cancel' or 'sale_update'
                        'quantity'          => $returnedQty,
                        'balance_after'     => $freshBalance,
                        'invoice_id'        => $invoice->id,
                        'user_id'           => $userId ?? Auth::id() ?? 1,
                    ]);
                }
            } else {
                // إرجاع المنتج المباشر
                $product->increment('quantity', $qty);
            }
        }
    }

    /**
     * التحقق المسبق من توفر الخامات قبل الشروع في إتمام الطلب (Pre-check)
     */
    public function checkAvailability(array $items): array
    {
        $shortages = [];
        $rawRequirements = [];

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $qty = (int) ($item['qty'] ?? 1);

            if (!$productId) {
                continue;
            }

            $product = Product::with('recipes.inventoryItem')->find($productId);
            if (!$product) {
                continue;
            }

            if ($product->recipes->isNotEmpty()) {
                foreach ($product->recipes as $recipe) {
                    if (!$recipe->inventory_item_id) {
                        continue;
                    }
                    $needed = (float) $recipe->quantity_used * $qty;
                    $rawRequirements[$recipe->inventory_item_id] = ($rawRequirements[$recipe->inventory_item_id] ?? 0.0) + $needed;
                }
            } else {
                if ((int) $product->quantity < $qty) {
                    $shortages[] = [
                        'type'      => 'product',
                        'name'      => $product->name,
                        'required'  => $qty,
                        'available' => $product->quantity,
                    ];
                }
            }
        }

        if (!empty($rawRequirements)) {
            $inventoryItems = InventoryItem::whereIn('id', array_keys($rawRequirements))->get()->keyBy('id');
            foreach ($rawRequirements as $itemId => $needed) {
                $item = $inventoryItems->get($itemId);
                $available = $item ? (float) $item->quantity : 0;
                if ($available < $needed) {
                    $shortages[] = [
                        'type'      => 'inventory_item',
                        'name'      => $item?->name ?? "خامة #{$itemId}",
                        'unit'      => $item?->unit ?? '',
                        'required'  => $needed,
                        'available' => $available,
                    ];
                }
            }
        }

        return $shortages;
    }
}
