<?php

use App\Http\Controllers\ExpenseDraftController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth',])->group(function () {
  Route::get('/expense-drafts', [ExpenseDraftController::class, 'index'])->name('expense-drafts.index');
  Route::post('/expense-drafts', [ExpenseDraftController::class, 'store'])->name('expense-drafts.store');
  Route::get('/draft-expenses', [ExpenseDraftController::class, "create"])->name('admin_draft.create')->middleware('admin');
  Route::put('expense-drafts/{draft}', [ExpenseDraftController::class, 'update'])
    ->name('expense-drafts.update');
  Route::delete('expense-drafts/{draft}', [ExpenseDraftController::class, 'destroy'])
    ->name('expense-drafts.destroy');
  Route::post('/expense-drafts/bulk-delete', [ExpenseDraftController::class, 'bulkDelete'])
    ->name('expense-drafts.bulk-delete')->middleware('admin');
});
