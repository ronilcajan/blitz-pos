<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class NotificationController extends Controller
{
    public function unread()
    {
        $userId = auth()->user()->id;

        $lastActivity = Activity::where('causer_id', $userId)
                    ->whereNull('read_at')
                    ->latest()->get()
                    ->map(function($log){
                        return [
                            'id' => $log->id,
                            'log_name' => $log->log_name,
                            'description' => $log->description,
                            'event' => $log->event,
                            'created_at' => $log->created_at->tz(session('timezone'))->format('M d, Y h:i A'),
                            'user' => User::find($log->causer_id)->name,
                            'user_image' => User::find($log->causer_id)->images[0] ?? asset('logo.png'),
                        ];
                    }); //returns the last logged activity

        return response()->json($lastActivity);

    }

    public function mark_as_read()
    {
        $userId = auth()->user()->id;

        // Mark all unread activities for the user as read
        $result = Activity::where('causer_id', $userId)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
    
        // Return a success response with the number of updated records
        return response()->json([
            'message' => 'Activities marked as read.',
            'updated_count' => $result
        ]);

    }
}
