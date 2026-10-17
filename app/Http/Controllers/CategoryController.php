<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
   // 1. حفظ صنف جديد (Create)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'تم إضافة الصنف بنجاح!');
    }

    // 2. تحديث صنف حالي (Update)
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'تم تحديث الصنف بنجاح!');
    }

    // 3. حذف الصنف (Delete)
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->back()->with('success', 'تم حذف الصنف بنجاح!');
    }
}
