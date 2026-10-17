<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * جلب البيانات والبحث عبر الـ AJAX
     * يغطي عمليتي البحث وعرض البيانات الأولي بالصفحة
     */
    public function ajaxSearch(Request $request)
    {
        $query = $request->get('q');

        // جلب المستخدمين بناءً على محددات البحث (الاسم، البريد، أو الـ ID)
        $users = User::query()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('id', '=', $query);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * إنشاء حساب جديد (Create)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['user', 'admin'])],
        ]);

        // عمل Hash لكلمة المرور تلقائياً يتم عبر الـ User Model Cast المتواجد لديك بمشروعك
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'], // سيتم تشفيرها تلقائياً بالـ Model Cast
            'role'     => $validated['role'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'user'    => $user
        ], 201);
    }

    /**
     * تحديث بيانات الحساب والصلاحية (Update)
     * يتعامل مع تحديث الـ Dropdown السريع، وتحديث الـ Form العام
     */
    public function update(Request $request, User $user)
    {
        // حماية Backend: منع الأدمن الحالي من تغيير صلاحيات نفسه لضمان عدم قفل لوحة التحكم
        if (auth()->id() === $user->id && $request->has('role') && $request->role !== $user->role) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تعديل مستوى صلاحية حسابك الشخصي منعاً لفقدان الوصول.'
            ], 403);
        }

        // التحقق من البيانات (يدعم التحديث الجزئي للصلاحية فقط أو البيانات كاملة)
        $validated = $request->validate([
            'name'  => [Rule::requiredIf($request->has('name')), 'string', 'max:255'],
            'email' => [Rule::requiredIf($request->has('email')), 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'  => [Rule::requiredIf($request->has('role')), Rule::in(['user', 'admin'])],
        ]);

        // تحديث البيانات الممررة فقط بسلاسة
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث البيانات بنجاح',
            'user'    => $user
        ]);
    }

    /**
     * حذف حساب نهائياً (Delete)
     */
    public function destroy(User $user)
    {
        // حماية Backend مضاعفة: منع الأدمن من الانتحار البرمجي وحذف نفسه
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'الإجراء مرفوض! لا يمكنك حذف حسابك الشخصي الذي تسجل الدخول به حالياً.'
            ], 403);
        }

        // تنفيذ الحذف النهائي
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح من النظام.'
        ]);
    }
}