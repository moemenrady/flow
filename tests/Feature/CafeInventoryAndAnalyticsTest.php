<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InventoryService;

test('admin can access comprehensive analytics and view rendered metrics', function () {
    $admin = User::firstOrCreate(
        ['email' => 'admin_test@flow.local'],
        ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin']
    );

    $response = $this->actingAs($admin)->get(route('analytics.comprehensive'));
    $response->assertOk();
    $response->assertSee('التحليل الشامل التنفيذي');
});

test('admin can access recipes index and inventory index', function () {
    $admin = User::firstOrCreate(
        ['email' => 'admin_test@flow.local'],
        ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin']
    );

    $this->actingAs($admin)->get(route('recipes.index'))->assertOk();
    $this->actingAs($admin)->get(route('inventory.index'))->assertOk();
    $this->actingAs($admin)->get(route('purchase-invoices.index'))->assertOk();
    $this->actingAs($admin)->get(route('purchase-invoices.create'))->assertOk();
});

test('product recipe live cost and automated stock deduction and refund cycle', function () {
    $admin = User::firstOrCreate(
        ['email' => 'admin_test@flow.local'],
        ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin']
    );

    // 1. خامة بن وحليب
    $coffee = InventoryItem::create([
        'item_code' => 'COF-' . uniqid(),
        'name' => 'بن خام بيست ' . uniqid(),
        'category' => 'قهوة',
        'unit' => 'جرام',
        'unit_price' => 0.50,
        'quantity' => 500,
        'reorder_level' => 50,
    ]);

    // 2. منتج بوصفة
    $category = Category::first() ?? Category::create(['name' => 'مشروبات']);
    $product = Product::create([
        'name' => 'موكا مثلج ' . uniqid(),
        'price' => 50.00,
        'category_id' => $category->id,
        'quantity' => 0,
        'is_produced' => true,
    ]);

    Recipe::create([
        'product_id' => $product->id,
        'inventory_item_id' => $coffee->id,
        'quantity_used' => 20, // 20g
    ]);

    $product->refresh();
    expect($product->hasRecipe())->toBeTrue();
    expect($product->recipe_cost)->toBe(10.0); // 20 * 0.50 = 10

    // 3. بيع وخصم تلقائي
    $invoice = Invoice::create([
        'invoice_number' => 'INV-PEST-' . uniqid(),
        'created_by' => $admin->id,
        'type' => 'product',
        'status' => 'paid',
        'total' => 100.00,
        'profit' => 80.00,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_type' => 'product',
        'product_id' => $product->id,
        'name' => $product->name,
        'qty' => 2, // 2 * 20 = 40g
        'price' => 50.00,
        'cost' => 10.00,
        'total' => 100.00,
    ]);

    $inventoryService = app(InventoryService::class);
    $inventoryService->deductForInvoice($invoice, $admin->id);

    $coffee->refresh();
    expect((float)$coffee->quantity)->toBe(460.0);

    // 4. استرجاع الفاتورة
    $inventoryService->restoreForInvoice($invoice, 'sale_cancel', $admin->id);
    $coffee->refresh();
    expect((float)$coffee->quantity)->toBe(500.0);
});
