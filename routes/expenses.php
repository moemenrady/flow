<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
  //المصروف 
  Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
  Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
  Route::post('/expenses/store', [ExpenseController::class, "store"])->name('expense.store');
  Route::post('/expense-drafts/{draft}/convert', [ExpenseController::class, 'convertFromDraft'])->name('expense-drafts.convert');
  Route::get('/expenses/ajaxSearch', [ExpenseController::class, 'ajaxSearch'])->name('expense.ajaxSearch');
  Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
    ->name('expenses.edit');

  Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])
    ->name('expenses.update');
});
