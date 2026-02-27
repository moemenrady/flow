<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ShiftController extends Controller
{
  public function deposit(Request $request, $shiftId)
  {
    $shift = Shift::findOrFail($shiftId);

    // 1️⃣ تأكد إن الشيفت مقفول
    if ($shift->end_time === null) {
      return back()->with('error', 'لا يمكن توريد شيفت لم يتم إغلاقه');
    }

    // 2️⃣ تأكد إنه متوردش قبل كده
    if ($shift->deposited_at !== null) {
      return back()->with('error', 'تم توريد هذا الشيفت بالفعل');
    }

    // 3️⃣ حساب مبلغ التوريد
    $depositAmount = $shift->total_amount - $shift->total_expense;

    if ($depositAmount < 0) {
      return back()->with('error', 'المصروفات أكبر من الإيرادات');
    }

    // 4️⃣ حفظ بيانات التوريد
    $shift->update([
      'deposited_amount' => $depositAmount,
      'deposited_at' => now(),
    ]);

    return back()->with('success', 'تم توريد مبلغ ' . $depositAmount . ' بنجاح');
  }
  public function show(Shift $shift)
  {
    $shift->load(['user', 'actions.invoice', 'actions.expenseDraft']);

    // حسابات مختصرة
    $totalIncome = $shift->actions()->sum('amount');
    $totalExpense = $shift->actions()->sum('expense_amount');
    $totalNet = $totalIncome - $totalExpense;

    // ترتيب actions حسب الوقت (أحدث أول/قديم أول) — افترض created_at
    $actions = $shift->actions()->orderBy('created_at', 'asc')->get();

    return view('daily.user_shifts.show', compact('shift', 'actions', 'totalIncome', 'totalExpense', 'totalNet'));
  }


  public function index(Request $request)
  {
    $user = Auth::user();

    // قاعدة الاستعلام: شيفتات المستخدم مرتبة تنازلياً حسب created_at
    $query = Shift::where('user_id', $user->id)->orderByDesc('created_at');

    // فلترة بالنطاق الزمني (من - إلى) — نستخدم created_at و updated_at حسب طلبك
    if ($request->filled('from') || $request->filled('to')) {
      // نحسب from/to بشكل مرن: لو مفقود نضع حدود واسعة
      $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::minValue();
      $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::maxValue();

      $query->where(function ($q) use ($from, $to) {
        $q->whereBetween('created_at', [$from, $to])
          ->orWhereBetween('updated_at', [$from, $to])
          ->orWhere(function ($q2) use ($from, $to) {
            // شيفت بدأ قبل النطاق وانتهى بعد النطاق
            $q2->where('created_at', '<', $from)
              ->where('updated_at', '>', $to);
          });
      });
    }

    $shifts = $query->get();

    // لو الطلب AJAX (الـ fetch من الـ front) نعيد JSON مهيأ
    if ($request->ajax() || $request->wantsJson()) {
      $data = $shifts->map(function ($shift) {
        // start_time نأخذه من created_at
        $start = $shift->created_at ? $shift->created_at->format('Y-m-d H:i') : '—';

        // end_time نقرأه من updated_at لكن فقط إذا عمود end_time موجود وغير فارغ (يعني الشيفت اتقفل)
        // افترضت أن عندك عمود boolean/nullable اسمه end_time أو منطق يحدد الإغلاق.
        $end = ($shift->end_time ?? null) ? $shift->updated_at->format('Y-m-d H:i') : '—';

        // مدة (إن وُجدت) — ضع المنطق الذي يناسبك (هنا افترضنا $shift->duration بالوحدة الدقيقة)
        $duration = $shift->duration ? ((int) $shift->duration) . ' دقيقة' : '—';

        // مبالغ بصيغة رقمية مُنسقة كسلاسل
        $totalAmount = number_format($shift->total_amount ?? 0, 2);
        $totalExpense = number_format($shift->total_expense ?? 0, 2);
        $netProfit = number_format((($shift->total_amount ?? 0) - ($shift->total_expense ?? 0)), 2);

        return [
          'id' => $shift->id,
          'start_time' => $start,
          'end_time' => $end,
          'duration' => $duration,
          'total_amount' => $totalAmount,
          'total_expense' => $totalExpense,
          'net_profit' => $netProfit,
        ];
      });

      return response()->json($data);
    }

    // طلب عادي → نعيد الـ view مع المتغيرات اللازمة
    return view('daily.user_shifts.index', compact('shifts'));
  }


  /**
   * ✅ تنسيق المدة (minutes -> نص)
   */
  private function formatDuration($minutes)
  {
    if ($minutes < 60) {
      return $minutes . ' دقيقة';
    }

    $hours = intdiv($minutes, 60);
    $remaining = $minutes % 60;

    if ($remaining == 0) {
      return $hours . ' ساعة';
    } elseif ($remaining <= 15) {
      return $hours . ' ساعة وربع';
    } elseif ($remaining <= 30) {
      return $hours . ' ساعة ونصف';
    } elseif ($remaining <= 45) {
      return $hours . ' ساعة وثلاثة أرباع';
    } else {
      return $hours . ' ساعة و' . $remaining . ' دقيقة';
    }
  }


  public function create()
  {
    $user = Auth::user();
    $today = Carbon::today();

    // 🔹 الشيفت المفتوح (لو موجود)
    $shift = Shift::with('actions')
      ->where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    // 🔹 آخر شيفت اتقفل النهارده وقيمته > 0
    $lastClosedShiftToday = Shift::
      whereNotNull('end_time')
      ->whereDate('start_time', $today)
      ->whereDate('end_time', $today)
      ->where('deposited_amount', '>', 0) // فقط المبالغ > 0
      ->orderByDesc('end_time')
      ->first();

    // 🔹 قيمة التوريد للشيفت ده (0 لو مفيش شيفت أو كل الشيفتات صفر)
    $lastDepositedAmount = $lastClosedShiftToday->deposited_amount ?? 0;

    if ($shift) {
      $totalCash = ShiftAction::where('shift_id', $shift->id)
        ->where('payment_type', 'cash')
        ->sum('amount');

      $totalDigital = ShiftAction::where('shift_id', $shift->id)
        ->where('payment_type', 'digital')
        ->sum('amount');

      return view('daily.user_shifts.create', compact(
        'shift',
        'totalCash',
        'totalDigital',
        'lastDepositedAmount',
        'lastClosedShiftToday'
      ));
    }

    return view('daily.user_shifts.create', compact(
      'shift',
      'lastDepositedAmount',
      'lastClosedShiftToday'
    ));
  }


  public function startFromLogin(Request $request)
  {
    $user = $request->user();
    // إنشئ شيفت جديد أو ارجع خطأ لو فيه شيفت مفتوح
    $shift = Shift::create([
      'user_id' => $user->id,
      'start_time' => now(),
      // حقول إضافية حسب المابلكيشن
    ]);

    return response()->json(['id' => $shift->id, 'shift' => $shift]);
  }

  // فتح شيفت جديد
  public function startShift()
  {
    $user = Auth::user();

    // شيك لو فيه شيفت مفتوح
    $activeShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    if ($activeShift) {

      return redirect()->back()->with('error', 'عندك شيفت مفتوح بالفعل');
    }

    $shift = Shift::create([
      'user_id' => $user->id,
      'start_time' => now(),
    ]);

    // بعد الفتح، رجّعه خطوة للورا وامسح الـ history
    return redirect()->back()->with('success', 'تم فتح الشيفت بنجاح')
      ->with('clear_history', true);
  }
  public function prompt()
  {
    $user = \Auth::user();

    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    if ($openShift) {

      return redirect()->route('main.create');
    }

    // اظهر الصفحة اللي تسأل المستخدم إذا يريد بدء شيفت الآن
    return view('daily.user_shifts.prompt');
  }

  // إنهاء الشيفت
  public function endShift(Request $request)
  {
    $user = Auth::user();

    $shift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    if (!$shift) {

      return redirect()->back()->with('error', 'مفيش شيفت مفتوح حاليا');
    }

    // تحديث الشيفت
    $shift->update([
      'end_time' => now(),
      'deposited_amount' => $request->safe_amount ?? 0
    ]);

    // حساب صافي الربح
    $netProfit = $shift->total_amount - $shift->total_expense;

    return redirect()->back()->with([
      'success' => 'تم إنهاء الشيفت',
      'net_profit' => $netProfit,
      'clear_history' => true,
    ]);
  }


  public function checkOpen(Request $request)
  {
    $user = Auth::user();

    // افتراض: الشيفت المفتوح هو اللي end_time فيه NULL
    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();
    $isAdmin = $user->hasRole('admin');
    if ($openShift && !$isAdmin) {
      return response()->json([
        'open' => true,
        'shift_id' => $openShift->id,
        // ممكن ترجع أي بيانات إضافية لو احتجت
      ]);
    }

    return response()->json(['open' => false]);
  }

  // عرض الشيفت الحالي والأكشنز
  public function currentShift()
  {
    $user = Auth::user();

    $shift = Shift::with('actions')->where('user_id', $user->id)->whereNull('end_time')->first();

    if (!$shift) {
      return response()->json(['message' => 'مفيش شيفت مفتوح حاليا'], 404);
    }

    return response()->json([
      'shift' => $shift,
      'total_income' => $shift->total_amount,
      'total_expense' => $shift->total_expense,
      'net_profit' => $shift->net_profit,
    ]);
  }
}
