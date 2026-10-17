<?php

// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Models\SystemAction;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\ShiftService;
use App\Support\InvoiceNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceController extends Controller
{
  public function index()
  {
    $invoices = Invoice::with(['items', 'client', 'creator'])->latest()->get();
    return view('invoices.index', compact('invoices'));
  }

  public function show(Invoice $invoice)
  {
    $invoice->load(['items', 'user', 'client', 'refunder', 'creator', 'shift']);

    $total = $invoice->items->sum('total');
    $cost = $invoice->items->sum('cost');
    $profit = $total - $cost;

    return view('invoices.show', compact('invoice', 'total', 'cost', 'profit'));
  }

  public function admin_show(Invoice $invoice)
  {
    $invoice->load(['items', 'user', 'client', 'refunder', 'creator', 'shift']);

    $total = $invoice->total ?? $invoice->items->sum('total');
    $cost = $invoice->items->sum('cost');
    $profit = $invoice->profit ?? ($total - $cost);
    $canRefund = $this->canRefundInvoice(Auth::user(), $invoice);

    return view('invoices.admin-show', compact('invoice', 'total', 'cost', 'profit', 'canRefund'));
  }

  public function client_show(Invoice $invoice)
  {
    $invoice->load([
      'client',
      'creator',
      'refunder',
      'shift',
      'items',
      'booking' => function ($query) {
        $query->with(['hall']);
      },
    ]);

    $groupedItems = [
      'product'      => $invoice->items->where('item_type', 'product'),
      'subscription' => $invoice->items->where('item_type', 'subscription'),
      'booking'      => $invoice->items->where('item_type', 'booking'),
      'session'      => $invoice->items->where('item_type', 'session'),
      'deposit'      => $invoice->items->where('item_type', 'deposit'),
    ];

    $purchaseItems = collect();
    if ($groupedItems['product']->isNotEmpty()) {
      $purchaseItems = $groupedItems['product'];
    }

    $totalAmount = $invoice->total;
    $invoiceType = $invoice->type;
    $extraData = [];
    $isHasPurchase = false;
    $bookingData = null;
    $hourlyRate = 0;
    $actualDurationMinutes = 0;
    $sessionData = null;

    if ($invoiceType === 'booking' || $invoiceType === 'mixed') {
      $bookingData = $invoice->booking;
      if ($bookingData) {
        $hourlyRate = $bookingData->base_hour_price;
        if (!empty($bookingData->real_start_at) && !empty($bookingData->real_end_at)) {
          $start = Carbon::parse($bookingData->real_start_at);
          $end = Carbon::parse($bookingData->real_end_at);
          $actualDurationMinutes = $end->diffInMinutes($start);
        }
      }
    }

    $canRefund = $this->canRefundInvoice(Auth::user(), $invoice);

    return view('invoices.client_show', [
      'invoice'               => $invoice,
      'groupedItems'          => $groupedItems,
      'totalAmount'           => $totalAmount,
      'invoiceType'           => $invoiceType,
      'extraData'             => $extraData,
      'isHasPurchase'         => $isHasPurchase,
      'bookingData'           => $bookingData,
      'hourlyRate'            => $hourlyRate,
      'actualDurationMinutes' => $actualDurationMinutes,
      'sessionData'           => $sessionData,
      'purchaseItems'         => $purchaseItems,
      'canRefund'             => $canRefund,
    ]);
  }

  public function ajaxSearch(Request $request)
  {
    $query = Invoice::query()->with('client')->where('total', '>', 0);

    if ($q = $request->query('q')) {
      $query->where(function ($sq) use ($q) {
        $sq->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$q}%"))
           ->orWhere('invoice_number', 'like', "%{$q}%");
      });
    }

    if ($types = $request->query('types')) {
      $types = explode(',', $types);
      $query->whereIn('type', $types);
    }

    if ($from = $request->query('from')) {
      $query->whereDate('created_at', '>=', $from);
    }
    if ($to = $request->query('to')) {
      $query->whereDate('created_at', '<=', $to);
    }

    $invoices = $query->latest()->take(50)->get();

    return response()->json($invoices->map(function ($inv) {
      return [
        'id'             => $inv->id,
        'invoice_number' => $inv->invoice_number,
        'client_name'    => $inv->client->name ?? 'عميل غير معروف',
        'type'           => $inv->type,
        'status'         => $inv->status ?? 'paid',
        'is_refunded'    => $inv->isRefunded(),
        'total'          => (float) $inv->total,
        'created_at'     => $inv->created_at->format('Y-m-d H:i'),
        'updated_at'     => $inv->updated_at->format('Y-m-d H:i'),
      ];
    }));
  }

  public function store(
    StoreInvoiceRequest $request,
    ShiftService $shiftService,
    InvoiceService $invoiceService,
    InventoryService $inventoryService
  ) {
    $validated = $request->validated();
    $user = auth()->user();

    $requestId = $request->input('request_id') ?? (string) \Str::uuid();

    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    $isAdmin = $user->hasRole('admin') || $user->isManager();

    if (!$openShift && !$isAdmin) {
      return response()->json([
        'status'       => 'error',
        'message'      => '⚠️ لا يوجد شيفت مفتوح لك حالياً',
        'requireShift' => true
      ], 400);
    }

    [$items, $totals] = $this->buildItemsFromRequest($validated['items']);
    $type = $this->determineInvoiceType($items);

    try {
      $invoice = DB::transaction(function () use (
        $requestId,
        $validated,
        $items,
        $totals,
        $type,
        $user,
        $openShift,
        $invoiceService,
        $inventoryService
      ) {
        // 1. فحص التكرار (Idempotency)
        $existing = Invoice::where('request_id', $requestId)
          ->lockForUpdate()
          ->first();

        if ($existing) {
          return $existing->load('items');
        }

        // 2. حساب رقم الدورة (Cycle Number)
        $cycleNumber = null;
        if ($type === 'product') {
          $lastCycle = Invoice::where('type', 'product')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('cycle_number');

          $cycleNumber = !$lastCycle ? 1 : ($lastCycle == 20 ? 1 : $lastCycle + 1);
        }

        // 3. إنشاء الفاتورة
        $invoice = $invoiceService->create([
          'request_id'     => $requestId,
          'invoice_number' => InvoiceNumber::next(),
          'client_id'      => $validated['client_id'] ?? null,
          'created_by'     => $user->id,
          'shift_id'       => $openShift?->id,
          'type'           => $type,
          'status'         => 'paid',
          'cycle_number'   => $cycleNumber,
          'total'          => $totals['total'],
          'profit'         => $totals['profit'],
          'notes'          => $validated['notes'] ?? null,
          'items'          => $items
        ]);

        // 4. خصم المخزون عبر خدمة المخزون المركزية (الخامات والمنتجات مع القفل التزامني ومنع الـ Deadlock)
        $inventoryService->deductForInvoice($invoice, $user->id);

        // 5. تسجيل في سجل الرقابة SystemAction
        SystemAction::create([
          'user_id'         => $user->id,
          'action'          => SystemActionType::SALE_PROCESS->value,
          'actionable_type' => Invoice::class,
          'actionable_id'   => $invoice->id,
          'invoice_id'      => $invoice->id,
          'shift_id'        => $openShift?->id,
          'amount'          => $invoice->total,
          'note'            => "إنشاء فاتورة - #{$invoice->invoice_number}",
          'meta'            => json_encode([
            'items'  => $items,
            'totals' => $totals,
            'type'   => $invoice->type,
          ]),
          'ip'              => request()->ip(),
          'source'          => 'web',
        ]);

        return $invoice;
      });
    } catch (RuntimeException $e) {
      return response()->json([
        'status'  => 'error',
        'message' => $e->getMessage(),
      ], 400);
    }

    // 6. تسجيل حركة في الشيفت
    if ($invoice && $openShift) {
      ShiftAction::create([
        'shift_id'    => $openShift->id,
        'action_type' => 'separate_sale',
        'invoice_id'  => $invoice->id,
        'amount'      => $invoice->total,
        'notes'       => "فاتورة رقم {$invoice->invoice_number}",
      ]);
    }

    return response()->json([
      'success'        => true,
      'invoice_number' => $invoice->invoice_number,
      'date'           => $invoice->created_at->format('Y-m-d H:i'),
      'cycle_number'   => $invoice->cycle_number,
      'type'           => $invoice->type,
      'items'          => $invoice->items,
      'total'          => $invoice->total
    ]);
  }

  /**
   * التحقق من صلاحية استرجاع الفاتورة
   */
  public function canRefundInvoice(?User $user, Invoice $invoice): array
  {
    if (!$user) {
      return ['allowed' => false, 'message' => 'يجب تسجيل الدخول أولاً.'];
    }

    if ($invoice->isRefunded()) {
      return ['allowed' => false, 'message' => 'تم استرجاع هذه الفاتورة بالفعل سابقاً.'];
    }

    // المدير والمشرف لديهما صلاحية استرجاع كاملة
    if ($user->isManager()) {
      return ['allowed' => true, 'message' => null];
    }

    // قيود الموظف
    $activeShift = $user->activeShift;
    if (!$activeShift) {
      return ['allowed' => false, 'message' => 'لا يمكنك استرجاع الفاتورة لعدم وجود وردية نشطة ومفتوحة لك حالياً.'];
    }

    // إذا كانت الفاتورة مسجلة على شيفت مختلف أو مغلق
    if ($invoice->shift_id && (int) $invoice->shift_id !== (int) $activeShift->id) {
      return ['allowed' => false, 'message' => 'غير مصرح لك باسترجاع هذه الفاتورة؛ تخص وردية أخرى أو وردية مغلقة. يرجى مراجعة المشرف أو الإدارة.'];
    }

    return ['allowed' => true, 'message' => null];
  }

  /**
   * استرجاع الفاتورة بالكامل (Refund Flow)
   */
  public function refund(Request $request, $id, InventoryService $inventoryService)
  {
    $request->validate([
      'reason' => 'required|string|min:3|max:500',
    ], [
      'reason.required' => 'يجب توضيح سبب استرجاع الفاتورة.',
      'reason.min'      => 'سبب الاسترجاع يجب ألا يقل عن 3 أحرف.',
    ]);

    $invoice = Invoice::with(['items.product.recipes.inventoryItem', 'shift'])->findOrFail($id);
    $user = Auth::user();

    $check = $this->canRefundInvoice($user, $invoice);
    if (!$check['allowed']) {
      if ($request->wantsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'error' => $check['message']], 403);
      }
      return redirect()->back()->with('error', $check['message']);
    }

    return DB::transaction(function () use ($invoice, $user, $request, $inventoryService) {
      // 1. إعادة المواد الخام والمنتجات للمخزن مع تسجيل حركات sale_cancel
      $inventoryService->restoreForInvoice($invoice, 'sale_cancel', $user->id);

      // 2. تحديث حالة الفاتورة
      $invoice->update([
        'status'        => 'refunded',
        'refunded_at'   => now(),
        'refund_reason' => $request->reason,
        'refunded_by'   => $user->id,
      ]);

      // 3. إلغاء أي حجز مرتبط
      if ($invoice->booking_id) {
        Booking::where('id', $invoice->booking_id)->update(['status' => 'cancelled']);
      }

      // 4. تسجيل في سجل الرقابة SystemAction
      $roleLabel = $user->isManager() ? 'إدارة' : 'موظف';
      try {
        SystemAction::create([
          'user_id'         => $user->id,
          'action'          => SystemActionType::REFUND_INVOICE->value,
          'actionable_type' => Invoice::class,
          'actionable_id'   => $invoice->id,
          'invoice_id'      => $invoice->id,
          'shift_id'        => $user->activeShift?->id ?? $invoice->shift_id,
          'amount'          => -$invoice->total,
          'note'            => "قام [{$user->name}] ({$roleLabel}) باسترجاع الفاتورة رقم #{$invoice->invoice_number}. السبب: {$request->reason}",
          'meta'            => json_encode([
            'refund_reason'  => $request->reason,
            'total_refunded' => $invoice->total,
          ]),
          'ip'              => request()->ip(),
          'source'          => 'web',
        ]);
      } catch (\Throwable $e) {}

      // 5. تسجيل حركة استرجاع في الشيفت المفتوح
      $currentShift = $user->activeShift;
      if ($currentShift) {
        try {
          ShiftAction::create([
            'shift_id'     => $currentShift->id,
            'action_type'  => 'refund',
            'invoice_id'   => $invoice->id,
            'amount'       => -$invoice->total,
            'notes'        => "استرجاع فاتورة رقم {$invoice->invoice_number}. السبب: {$request->reason}",
          ]);
        } catch (\Throwable $e) {}
      }

      if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
          'success'  => true,
          'message'  => 'تم استرجاع الفاتورة بنجاح، وإعادة الخامات للمخزن، وتسجيل قيد الحركة.',
          'redirect' => route('invoices.client.show', $invoice->id),
        ]);
      }

      return redirect()->back()->with('success', 'تم استرجاع الفاتورة بنجاح وإعادة الخامات للمخزن.');
    });
  }

  private function buildItemsFromRequest(array $requestItems): array
  {
    $items = [];
    $grandTotal = 0.0;
    $grandProfit = 0.0;

    foreach ($requestItems as $in) {
      $type = $in['item_type'];

      if ($type === 'product') {
        $product = Product::findOrFail($in['product_id']);
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) $product->price;
        $cost = (float) $product->actual_cost;

        $total = $price * $qty;
        $profit = ($price - $cost) * $qty;

        $items[] = [
          'item_type'  => 'product',
          'product_id' => $product->id,
          'name'       => $product->name,
          'qty'        => $qty,
          'price'      => $price,
          'cost'       => $cost,
          'total'      => $total,
        ];
      } elseif ($type === 'subscription') {
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type'       => 'subscription',
          'subscription_id' => $in['subscription_id'] ?? null,
          'name'            => $in['name'] ?? 'اشتراك',
          'qty'             => $qty,
          'price'           => $price,
          'cost'            => $cost,
          'total'           => $price * $qty,
        ];
      } elseif ($type === 'booking') {
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type'  => 'booking',
          'booking_id' => $in['booking_id'] ?? null,
          'name'       => $in['name'] ?? 'حجز قاعة',
          'qty'        => $qty,
          'price'      => $price,
          'cost'       => $cost,
          'total'      => $price * $qty,
        ];
      } elseif ($type === 'session') {
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type'  => 'session',
          'session_id' => $in['session_id'] ?? null,
          'name'       => $in['name'] ?? 'جلسة',
          'qty'        => $qty,
          'price'      => $price,
          'cost'       => $cost,
          'total'      => $price * $qty,
        ];
      } elseif ($type === 'deposit') {
        $amount = (float) $in['amount'];
        $desc = $in['description'] ?? null;

        $items[] = [
          'item_type'   => 'deposit',
          'name'        => 'دفعة مقدمة',
          'qty'         => 1,
          'price'       => $amount,
          'cost'        => 0,
          'total'       => $amount,
          'description' => $desc,
        ];
      }

      $grandTotal += end($items)['total'];
      $grandProfit += (end($items)['price'] - end($items)['cost']) * end($items)['qty'];
    }

    return [$items, ['total' => round($grandTotal, 2), 'profit' => round($grandProfit, 2)]];
  }

  private function determineInvoiceType(array $items): string
  {
    $types = collect($items)->pluck('item_type')->unique()->values()->all();
    return count($types) === 1 ? $types[0] : 'mixed';
  }

  public function print(Request $request)
  {
    $items = collect($request->items)->map(fn($item) => [
      'qty'   => $item['qty'],
      'name'  => $item['name'],
      'total' => $item['qty'] * $item['price'],
    ]);
    return view('sale_proccess.print', compact('items'));
  }

  public function clientInvoices(Request $request, $clientId)
  {
    $client = Client::findOrFail($clientId);
    $invoice = Invoice::where('client_id', $client->id);
    $invoiceCount = $invoice->count();
    $invoiceTotal = $invoice->sum('total');
    $invoices = $invoice->select('id', 'type', 'invoice_number', 'total')->get();
    return view('clients.invoices', compact('invoiceCount', 'invoiceTotal', 'invoices', 'client'));
  }
}
