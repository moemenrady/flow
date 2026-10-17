<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ImportantProduct;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'recipes.inventoryItem'])->latest()->get();
        $countProducts = $products->count();
        $countItems = $products->sum('quantity');
        $categories = Category::all();
        $inventories = \App\Models\InventoryItem::orderBy('name')->get();

        return view('products.index', compact(
            'products',
            'countProducts',
            'countItems',
            'categories',
            'inventories'
        ));
    }



    public function create()
    {
        return view('products.create');
    }
    public function createImportant()
    {

        $importantProducts = ImportantProduct::with('product')->latest()->paginate(20);
        // $products not mandatory here (we use AJAX search), لكن لو بدك تمرر:
        // $products = \App\Models\Product::take(30)->get();

        return view('managment.changes.important_products.create', compact('importantProducts'));
    }
    public function storeImportant(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
        ]);

        // التحقق إذا الاسم موجود بالفعل
        $nameExists = ImportantProduct::where('name', $data['name'])->exists();
        if ($nameExists) {
            return redirect()->back()->with('error', 'هذا الاسم مرتبط بمنتج مهم بالفعل.');
        }

        // التحقق إذا المنتج مرتبط بالفعل
        $productExists = ImportantProduct::where('product_id', $data['product_id'])->exists();
        if ($productExists) {
            return redirect()->back()->with('error', 'هذا المنتج موجود بالفعل كمنتج مهم.');
        }

        // إنشاء المنتج المهم
        ImportantProduct::create([
            'product_id' => $data['product_id'],
            'name' => $data['name'],
        ]);

        return redirect()->back()->with('success', 'تم حفظ المنتج المهم بنجاح.');
    }
    public function updateImportant(Request $request, ImportantProduct $importantProduct)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
        ]);

        // تحقق من الاسم المكرر (باستثناء نفسه)
        $existsByName = ImportantProduct::where('name', $data['name'])
            ->where('id', '!=', $importantProduct->id)
            ->exists();
        if ($existsByName) {
            return redirect()->back()->with('error', 'هذا الاسم مستخدم بالفعل.');
        }

        // تحقق من المنتج المرتبط (باستثناء نفسه)
        if (!empty($data['product_id'])) {
            $existsByProduct = ImportantProduct::where('product_id', $data['product_id'])
                ->where('id', '!=', $importantProduct->id)
                ->exists();
            if ($existsByProduct) {
                return redirect()->back()->with('error', 'هذا المنتج مرتبط بالفعل بمنتج مهم آخر.');
            }
        }

        // التحديث
        $importantProduct->update([
            'name' => $data['name'],
            'product_id' => $data['product_id'] ?? $importantProduct->product_id,
        ]);

        return redirect()->back()->with('success', 'تم تحديث المنتج المهم بنجاح.');
    }
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // 1. حذف ارتباطه من جدول المنتجات المهمة أولاً
            $product->important_products()->delete();

            // 2. التحقق من وجود عمليات شراء معتمدة على هذا المنتج
            if ($product->purchases()->exists()) {
                return redirect()->back()->with('error', 'لا يمكن حذف هذا المنتج لوجود عمليات شراء مسجلة عليه بالكامل.');
            }

            // 3. حذف المنتج النهائي
            $product->delete();

            DB::commit();

            // توجيه المستخدم لصفحة المنتجات الرئيسية مع رسالة نجاح
            return redirect()->route('products.index')->with('success', 'تم حذف المنتج وكافة تعريفاته بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ غير متوقع أثناء محاولة حذف المنتج.');
        }
    }
    public function show(Product $product)
    {
        $importantProduct = ImportantProduct::where('product_id', $product->id)->first();

        return view('products.show', compact('product', 'importantProduct'));
    }

    public function addQuantityPage()
    {
        return view('products.add-quantity');
    }
    public function store(Request $request)
    {
        // 1️⃣ Validation كامل
        $request->validate([
            'product_id'   => 'nullable|integer',
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'cost'         => 'nullable|numeric|min:0',
            'quantity'     => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'category_id'  => 'required|exists:categories,id',
            'is_produced'  => 'nullable|boolean',
        ]);

        $productId = $request->product_id;
        if (empty($productId)) {
            // توليد معرف تلقائي متزايد
            $maxId = Product::max('id') ?: 100;
            $productId = $maxId + 1;
        } else {
            if (Product::where('id', $productId)->exists()) {
                return redirect()->back()->with('error', "هذا المعرف (#{$productId}) موجود بالفعل لمنتج آخر.");
            }
        }

        // 3️⃣ تحقق من تكرار الاسم
        if (Product::where('name', $request->name)->exists()) {
            return redirect()->back()->with('error', "هذا المنتج مسجل بالفعل في المنيو.");
        }

        $isProduced = $request->boolean('is_produced');
        $cost = (float) ($request->cost ?? 0);
        $quantity = $isProduced ? 0 : (int) ($request->quantity ?? 0);
        $minQuantity = (int) ($request->min_quantity ?? 0);

        // 4️⃣ إدخال البيانات مع timestamps
        $product = Product::create([
            'id'           => $productId,
            'name'         => $request->name,
            'price'        => $request->price,
            'cost'         => $cost,
            'quantity'     => $quantity,
            'min_quantity' => $minQuantity,
            'category_id'  => $request->category_id,
            'is_produced'  => $isProduced ? 1 : 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        if ($isProduced) {
            return redirect()->route('recipes.index')
                ->with('success', "تم إضافة المنتج [{$product->name}] بنجاح! تم نقلك لمعيار الوصفات لإضافة مكونات الخامات وحساب التكلفة التلقائية.");
        }

        return redirect()->back()->with('success', 'تمت إضافة المنتج بنجاح ✅');
    }

    public function addQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($id);
        $product->quantity += $request->quantity;
        $product->save();

        return redirect()->route('products.index')->with('success', 'تمت إضافة الكمية بنجاح ✅');
    }
    public function searchId(Request $request)
    {
        $code = trim($request->get('query'));

        if (!$code || strlen($code) < 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'كود غير صالح'
            ], 422);
        }

        $products = Product::where('id', $code)
            ->select('id', 'name', 'price', 'cost', 'quantity')
            ->limit(2)
            ->get();

        if ($products->count() === 1) {
            return response()->json([
                'status' => 'success',
                'type' => 'single',
                'product' => $products->first()
            ]);
        }

        if ($products->count() > 1) {
            return response()->json([
                'status' => 'warning',
                'type' => 'multiple',
                'products' => $products
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'message' => 'المنتج غير موجود'
        ], 404);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

        $results = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('id', $query)
            ->select('id', 'name', 'price', 'cost', 'quantity', 'category_id') // هات اللي محتاجه
            ->limit(10)
            ->get();

        return response()->json($results);
    }
}
