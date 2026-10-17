<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Sation;
use App\Models\SetionNotAdded;

class StatusUpdater
{
    public static function run()
    {
        // حذف أي جلسات "لم تسجل" لو العميل عنده جلسة active
        $activeClientIds = Sation::where('status', 'active')
            ->pluck('client_id');

        SetionNotAdded::whereIn('client_id', $activeClientIds)->delete();

        $user = \Auth::user();
        // تحديث الاشتراكات المنتهية
        Subscription::where('is_active', true)
            ->where('end_date', '<=', now()) // كامل التاريخ والوقت
            ->update(['is_active' => false]);

        // تحديث الحجوزات scheduled اللي ميعاد بدايتها جه
        Booking::where('status', 'scheduled')
            ->where('start_at', '<=', now()) // كامل التاريخ والوقت
            ->update(['status' => 'due']);

        Subscription::where('is_active', true)
            ->whereNotNull('visit_date')
            ->where('visit_date', '<=', now()->subDay()->toDateString()) // لو عدّى يوم
            ->update(['attendees' => false]);
        Subscription::where('is_active', false)
            ->whereNotNull('visit_date')
            ->update(['attendees' => false]);

        $now = Carbon::now();
        $todayMidnight = Carbon::today(); // 00:00

        // أحدث شيفت مفتوح (إن وجد)
        $openShift = Shift::where('user_id', $user->id)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        /*
    |--------------------------------------------------------------------------
    | حالة الموظف (role = user)
    |--------------------------------------------------------------------------
    */
       

        /*
    |--------------------------------------------------------------------------
    | حالة الأدمن (admin)
    |--------------------------------------------------------------------------
    */
        if ($user->hasRole('admin')) {

            if ($openShift) {

                if (Carbon::parse($openShift->start_time)->lt($todayMidnight)) {

                    $openShift->end_time = $todayMidnight;
                    $openShift->duration = Carbon::parse($openShift->start_time)
                        ->diffInMinutes($todayMidnight);

                    $openShift->save();

                    ShiftAction::create([
                        'shift_id' => $openShift->id,
                        'action_type' => 'end_shift',
                        'notes' => 'تم غلق الشيفت تلقائياً عند بداية اليوم (تقسيم شيفت أدمن)',
                        'amount' => 0,
                        'expense_amount' => 0,
                    ]);

                    // فتح شيفت جديد للأدمن
                    $exists = Shift::where('user_id', $user->id)
                        ->whereNull('end_time')
                        ->exists();

                    if (!$exists) {

                        $newShift = Shift::create([
                            'user_id' => $user->id,
                            'start_time' => $todayMidnight,
                            'total_amount' => 0,
                            'total_expense' => 0,
                        ]);

                        ShiftAction::create([
                            'shift_id' => $newShift->id,
                            'action_type' => 'start_shift',
                            'notes' => 'تم فتح شيفت تلقائياً بعد بداية اليوم (أدمن)',
                            'amount' => 0,
                            'expense_amount' => 0,
                        ]);
                    }

                    return;
                }

                return;
            }

            // لو مفيش شيفت مفتوح للأدمن → افتح شيفت جديد
            $newShift = Shift::create([
                'user_id' => $user->id,
                'start_time' => $todayMidnight,
                'total_amount' => 0,
                'total_expense' => 0,
            ]);

            ShiftAction::create([
                'shift_id' => $newShift->id,
                'action_type' => 'start_shift',
                'notes' => 'تم فتح شيفت تلقائياً لبداية اليوم (أدمن)',
                'amount' => 0,
                'expense_amount' => 0,
            ]);
        }
    }
}
