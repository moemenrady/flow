<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // 1. المخزن وإدارة الخامات
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/{id}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // 2. سجل الحركات والتقارير
    Route::get('/inventory-movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory-daily-tracking', [InventoryController::class, 'dailyTracking'])->name('inventory.daily_tracking');
    Route::get('/inventory-export-tracking', [InventoryController::class, 'exportTrackingCsv'])->name('inventory.export_tracking');
    Route::get('/inventory-ajax-search', [PurchaseInvoiceController::class, 'searchInventoryItems'])->name('inventory.ajaxSearch');

    // 3. معيار الوصفات (Recipes)
    Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
    Route::get('/recipes/create', [RecipeController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');

    // 4. فواتير الشراء والتوريد (Purchase Invoices)
    Route::resource('purchase-invoices', PurchaseInvoiceController::class);
});
