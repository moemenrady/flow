<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\SystemAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * عرض قائمة الخامات في المخزن
     */
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $items = $query->latest()->get();

        // إحصائيات سريعة للبطاقات
        $allItems = InventoryItem::all();
        $totalValuation = $allItems->sum(fn($i) => (float) $i->quantity * (float) $i->unit_price);
        $totalItemsCount = $allItems->count();
        $lowStockCount = $allItems->filter(fn($i) => (float) $i->quantity > 0 && (float) $i->quantity <= (float) $i->reorder_level)->count();
        $outOfStockCount = $allItems->filter(fn($i) => (float) $i->quantity <= 0)->count();

        $categories = InventoryItem::whereNotNull('category')->distinct()->pluck('category')->filter()->values();

        return view('inventory.index', compact(
            'items',
            'allItems',
            'totalValuation',
            'totalItemsCount',
            'lowStockCount',
            'outOfStockCount',
            'categories'
        ));
    }

    /**
     * صفحة إضافة خامة جديدة
     */
    public function create()
    {
        $units = ['جرام', 'مل', 'قطعة', 'كجم', 'لتر', 'علبة', 'كيس', 'زجاجة', 'باكت'];
        $categories = InventoryItem::whereNotNull('category')->distinct()->pluck('category')->filter()->values();
        if ($categories->isEmpty()) {
            $categories = collect(['خامات ومشروبات', 'الألبان', 'البن والقهوة', 'مستهلكات وأكواب', 'حلويات ومعجنات']);
        }

        return view('inventory.create', compact('units', 'categories'));
    }

    /**
     * تخزين المادة الخام وتسجيل الرصيد الافتتاحي
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:inventory_items,name',
            'code'          => 'nullable|string|max:100|unique:inventory_items,code',
            'category'      => 'nullable|string|max:100',
            'quantity'      => 'required|numeric|min:0',
            'unit'          => 'required|string|max:50',
            'reorder_level' => 'required|numeric|min:0',
            'unit_price'    => 'required|numeric|min:0',
        ], [
            'name.required'   => 'يرجى إدخال اسم المادة الخام.',
            'name.unique'     => 'هذه المادة الخام مسجلة بالفعل.',
            'unit.required'   => 'يرجى تحديد وحدة القياس.',
        ]);

        return DB::transaction(function () use ($validated) {
            $item = InventoryItem::create($validated);

            // تسجيل أول حركة للمادة الخام (رصيد افتتاحي)
            if ($item->quantity > 0) {
                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'type'              => 'restock',
                    'quantity'          => $item->quantity,
                    'balance_after'     => $item->quantity,
                    'user_id'           => Auth::id() ?? 1,
                ]);
            }

            try {
                SystemAction::create([
                    'user_id'         => Auth::id(),
                    'action'          => SystemActionType::INVENTORY_RESTOCK->value,
                    'actionable_type' => InventoryItem::class,
                    'actionable_id'   => $item->id,
                    'note'            => "إضافة خامة جديدة: {$item->name} برصيد {$item->quantity} {$item->unit}",
                    'ip'              => request()->ip(),
                    'source'          => 'web',
                ]);
            } catch (\Throwable $e) {}

            return redirect()->route('inventory.index')->with('success', 'تم إضافة المادة الخام بنجاح وتسجيل الرصيد الافتتاحي.');
        });
    }

    /**
     * صفحة تعديل المادة الخام
     */
    public function edit($id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);
        $units = ['جرام', 'مل', 'قطعة', 'كجم', 'لتر', 'علبة', 'كيس', 'زجاجة', 'باكت'];
        $categories = InventoryItem::whereNotNull('category')->distinct()->pluck('category')->filter()->values();

        return view('inventory.edit', compact('inventoryItem', 'units', 'categories'));
    }

    /**
     * تحديث بيانات المادة الخام مع تسجيل فروق الأرصدة في الحركات
     */
    public function update(Request $request, $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:inventory_items,name,' . $id,
            'code'          => 'nullable|string|max:100|unique:inventory_items,code,' . $id,
            'category'      => 'nullable|string|max:100',
            'quantity'      => 'required|numeric|min:0',
            'unit'          => 'required|string|max:50',
            'reorder_level' => 'required|numeric|min:0',
            'unit_price'    => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($inventoryItem, $validated) {
            $oldQuantity = (float) $inventoryItem->quantity;

            $inventoryItem->update($validated);
            $newQuantity = (float) $inventoryItem->quantity;

            $difference = round($newQuantity - $oldQuantity, 2);

            // إذا تغيرت الكمية نسجل حركة تسوية
            if ($difference != 0) {
                InventoryMovement::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'type'              => $difference > 0 ? 'restock' : 'waste',
                    'quantity'          => $difference,
                    'balance_after'     => $newQuantity,
                    'user_id'           => Auth::id() ?? 1,
                ]);
            }

            return redirect()->route('inventory.index')->with('success', 'تم تحديث بيانات المادة الخام وتسجيل التغييرات في دفتر الحركات.');
        });
    }

    /**
     * تسوية سريعة للمخزون (إضافة توريد أو إهلاك وتالف)
     */
    public function adjust(Request $request, $id)
    {
        $request->validate([
            'type'   => 'required|in:restock,waste',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $item = InventoryItem::findOrFail($id);
        $amount = (float) $request->amount;

        return DB::transaction(function () use ($item, $request, $amount) {
            if ($request->type === 'restock') {
                $item->increment('quantity', $amount);
                $newQty = $item->quantity;
                $logQty = $amount;
                $msg = "تم تسجيل توريد ({$amount} {$item->unit}) بنجاح لصنف [{$item->name}].";
            } else {
                if ((float) $item->quantity < $amount) {
                    return redirect()->back()->with('error', "الكمية المراد إهلاكها أكبر من المتاح في المخزن حالياً ({$item->quantity})!");
                }
                $item->decrement('quantity', $amount);
                $newQty = $item->quantity;
                $logQty = -$amount;
                $msg = "تم تسجيل إهلاك وتلف ({$amount} {$item->unit}) لصنف [{$item->name}].";
            }

            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type'              => $request->type,
                'quantity'          => $logQty,
                'balance_after'     => $newQty,
                'user_id'           => Auth::id() ?? 1,
            ]);

            return redirect()->route('inventory.index')->with('success', $msg);
        });
    }

    /**
     * حذف آمن للمادة الخام (محمي من كسر وصفات المنتجات)
     */
    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);

        // 🛡️ حماية صارمة: منع حذف خامة مرتبطة بوصفات منتجات
        if ($item->recipes()->count() > 0) {
            return redirect()->route('inventory.index')->with('error', "لا يمكن حذف [{$item->name}] لأنها مرتبطة بمكونات وصفات منتجات! قم بإزالتها من وصفات المنتجات أولاً.");
        }

        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'تم حذف المادة الخام بنجاح من المخزن.');
    }

    /**
     * دفتر وسجل حركات المخزون الكامل
     */
    public function movements(Request $request)
    {
        $query = InventoryMovement::with([
            'inventoryItem',
            'invoice.items.product',
            'user'
        ]);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('inventory')) {
            $search = $request->inventory;
            $query->whereHas('inventoryItem', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('product')) {
            $prod = $request->product;
            $query->whereHas('invoice.items.product', function ($q) use ($prod) {
                $q->where('name', 'like', '%' . $prod . '%');
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $movements = $query->latest()->paginate(25)->withQueryString();

        // إحصائيات سريعة لليوم
        $todaySales = InventoryMovement::whereDate('created_at', today())
            ->whereIn('type', ['sale', 'sale_update'])
            ->count();

        $todayWaste = InventoryMovement::whereDate('created_at', today())
            ->where('type', 'waste')
            ->count();

        $todayRestock = InventoryMovement::whereDate('created_at', today())
            ->where('type', 'restock')
            ->count();

        $mostConsumed = InventoryMovement::whereDate('created_at', today())
            ->whereIn('type', ['sale', 'waste'])
            ->select('inventory_item_id', DB::raw('SUM(ABS(quantity)) as total_qty'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_qty')
            ->with('inventoryItem')
            ->first();

        $mostConsumedName = $mostConsumed && $mostConsumed->inventoryItem ? $mostConsumed->inventoryItem->name : 'لا توجد حركات اليوم';

        return view('inventory.movements', compact(
            'movements',
            'todaySales',
            'todayWaste',
            'todayRestock',
            'mostConsumedName'
        ));
    }

    /**
     * تقرير متابعة المخزون والتكاليف الشامل
     */
    public function dailyTracking(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $allItems = InventoryItem::orderBy('id')->get();
        $items = $query->orderBy('id')->get();

        $totalValuation = $allItems->sum(fn($i) => (float) $i->quantity * (float) $i->unit_price);
        $totalAvailableUnits = $allItems->where('quantity', '>', 0)->sum('quantity');
        $reorderItemsCount = $allItems->filter(fn($i) => (float) $i->quantity <= (float) $i->reorder_level)->count();
        $outOfStockItems = $allItems->filter(fn($i) => (float) $i->quantity <= 0);
        $lowStockItems = $allItems->filter(fn($i) => (float) $i->quantity > 0 && (float) $i->quantity <= (float) $i->reorder_level);
        $categoriesList = InventoryItem::whereNotNull('category')->distinct()->pluck('category')->filter()->values();

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $items = $items->filter(fn($i) => $i->status === $status);
        }

        return view('inventory.daily_tracking', compact(
            'items',
            'allItems',
            'totalValuation',
            'totalAvailableUnits',
            'reorderItemsCount',
            'outOfStockItems',
            'lowStockItems',
            'categoriesList'
        ));
    }

    /**
     * تصدير تقرير المخزون بصيغة CSV المتوافقة مع اللغة العربية
     */
    public function exportTrackingCsv()
    {
        $items = InventoryItem::orderBy('id')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="متابعة_المخزون_والتكاليف_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($items) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($output, [
                'كود الصنف',
                'اسم الصنف / البيان',
                'التصنيف',
                'الرصيد',
                'الوحدة',
                'تكلفة الوحدة',
                'القيمة الإجمالية',
                'حد الطلب',
                'الحالة',
            ]);

            $totalValue = 0;
            foreach ($items as $item) {
                $val = (float) $item->quantity * (float) $item->unit_price;
                $totalValue += $val;

                fputcsv($output, [
                    $item->item_code,
                    $item->name,
                    $item->category ?? 'عام',
                    $item->quantity,
                    $item->unit,
                    number_format($item->unit_price, 2, '.', ''),
                    number_format($val, 2, '.', ''),
                    $item->reorder_level,
                    $item->status,
                ]);
            }

            fputcsv($output, [
                'الإجمالي الكلي',
                '',
                '',
                '',
                '',
                '',
                number_format($totalValue, 2, '.', ''),
                '',
                '',
            ]);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
