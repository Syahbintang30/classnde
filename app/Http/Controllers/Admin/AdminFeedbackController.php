<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoachingBooking;

class AdminFeedbackController extends Controller
{
    public function index()
    {
        $this->authorize('admin');
    $items = CoachingBooking::with('user')->whereNotNull('notes')->orderByDesc('updated_at')->paginate(30);
    return view('admin.coaching_feedbacks.index', compact('items'));
    }

    public function update(Request $request, \App\Models\CoachingBooking $booking)
    {
        $this->authorize('admin');
        $adminActionMaxLength = config('constants.business_logic.admin_action_max_length');
        
        $data = $request->validate(['admin_action' => "nullable|string|max:{$adminActionMaxLength}"]);
        // store admin action into booking.admin_note (if column exists) else append to notes
        try {
            if (isset($booking->admin_note)) {
                $booking->admin_note = $data['admin_action'] ?? null;
            } else {
                $existing = $booking->notes ?? '';
                $booking->notes = trim(($existing ? $existing . "\n\n" : '') . ($data['admin_action'] ?? ''));
            }
            $booking->save();
        } catch (\Throwable $e) {
            logger()->warning('Failed to save admin action to booking', ['err' => $e->getMessage()]);
        }
        return redirect()->back()->with('success','Recommendation saved');
    }
}
