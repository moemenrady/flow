<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * صفحة معيار الوصفات وربط المنتجات بالخامات وحساب التكلفة
     */
    public function index()
    {
        $products = Product::with(['category', 'recipes.inventoryItem'])->latest()->get();
        $inventories = InventoryItem::orderBy('name')->get();

        return view('recipes.index', compact('products', 'inventories'));
    }

    /**
     * تحديث / حفظ مكونات وصفة المنتج بالكامل (معاملة ذرية)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'inventory_ids'   => 'nullable|array',
            'inventory_ids.*' => 'exists:inventory_items,id',
            'quantities'      => 'nullable|array',
            'quantities.*'    => 'numeric|min:0.001',
        ]);

        $product = Product::findOrFail($id);

        return DB::transaction(function () use ($request, $product) {
            // حذف المكونات القديمة لإعادة البناء بنظافة وبدون تكرار
            Recipe::where('product_id', $product->id)->delete();

            if ($request->has('inventory_ids') && $request->has('quantities')) {
                foreach ($request->inventory_ids as $index => $invId) {
                    if (isset($request->quantities[$index]) && (float) $request->quantities[$index] > 0) {
                        Recipe::create([
                            'product_id'        => $product->id,
                            'inventory_item_id' => $invId,
                            'quantity_used'     => (float) $request->quantities[$index],
                        ]);
                    }
                }
            }

            // تحديث تكلفة المنتج في عمود cost للتوافق التام
            $newRecipeCost = $product->fresh('recipes.inventoryItem')->recipe_cost;
            if ($newRecipeCost > 0) {
                $product->update(['cost' => $newRecipeCost]);
            }

            return redirect()->route('recipes.index')
                ->with('success', "تم حفظ وتحديث وصفة [{$product->name}] بنجاح، وتحديث تكلفة الصنف التلقائية ({$newRecipeCost} ج).");
        });
    }

    /**
     * فورم إضافة وصفة لمنتج
     */
    public function create()
    {
        $products = Product::all();
        $inventories = InventoryItem::all();

        return view('recipes.create', compact('products', 'inventories'));
    }

    /**
     * حفظ مكونات وصفة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'inventory_ids'   => 'required|array',
            'inventory_ids.*' => 'exists:inventory_items,id',
            'quantities'      => 'required|array',
            'quantities.*'    => 'numeric|min:0.001',
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::findOrFail($request->product_id);

            foreach ($request->inventory_ids as $index => $invId) {
                if (isset($request->quantities[$index]) && (float) $request->quantities[$index] > 0) {
                    Recipe::create([
                        'product_id'        => $product->id,
                        'inventory_item_id' => $invId,
                        'quantity_used'     => (float) $request->quantities[$index],
                    ]);
                }
            }

            $newRecipeCost = $product->fresh('recipes.inventoryItem')->recipe_cost;
            if ($newRecipeCost > 0) {
                $product->update(['cost' => $newRecipeCost]);
            }

            return redirect()->route('recipes.index')->with('success', 'تم حفظ مكونات الوصفة بنجاح.');
        });
    }

    /**
     * حذف مكون محدد من الوصفة
     */
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $productId = $recipe->product_id;
        $recipe->delete();

        $product = Product::find($productId);
        if ($product) {
            $product->update(['cost' => $product->recipe_cost]);
        }

        return redirect()->route('recipes.index')->with('success', 'تم إزالة المكون من الوصفة.');
    }
}
