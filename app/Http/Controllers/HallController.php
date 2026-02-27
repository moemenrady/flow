<!--

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Http\Request;

class HallController extends Controller
{
    /**
     * تخزين قاعة جديدة
     */
    public function store(Request $request)
    {
        // ✅ التحقق من المدخلات
        $validated = $request->validate([
            'name'          => 'required|string|max:50',
            'setter_name'=> 'required|string|max:50',
            'min_capacity'  => 'required|integer|min:1',
            'max_capacity'  => 'required|integer|gte:min_capacity',
            'is_active'     => 'required|boolean',
        ]);

        // ✅ الحفظ في قاعدة البيانات
        Hall::create($validated);

        // ✅ رجوع برسالة نجاح
        return redirect()->back()->with('success', 'تم إضافة القاعة بنجاح 🎉');
    }

    /**
     * عرض صفحة create مع القاعات
     */
    public function create()
    {
        $halls = Hall::latest()->get();
        return view('managment.changes.halls.create', compact('halls'));
    }
} -->
