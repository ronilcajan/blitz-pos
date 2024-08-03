<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogsController extends Controller
{
    public function index(){

        $storeId = auth()->user()->store_id;
        
        $storeCauserIds = User::where('store_id', $storeId)->pluck('id')->toArray();

        $logs = Activity::whereIn('causer_id', $storeCauserIds)
                        ->latest()
                        ->paginate(20)
                        ->withQueryString()
                        ->through(function ($log) {

                            $subject = $log->subject_type ? $log->subject_type::find($log->subject_id) : null;

                           // Initialize the next column key and value
                            $nextColumnKey = null;
                            $nextColumnValue = null;

                            if ($subject) {
                                // Retrieve the subject attributes as an array
                                $subjectAttributes = $subject->getAttributes(); // Get all attributes as an array
                                $keys = array_keys($subjectAttributes); // Get the keys (column names)
                                
                                // Get the second column key if it exists
                                if (isset($keys[1])) {
                                    $nextColumnKey = $keys[1];
                                    $nextColumnValue = $subject->{$nextColumnKey}; // Get the value of the next column
                                }
                            }

                            return [
                                'id' => $log->id,
                                'log_name' => $log->log_name,
                                'description' => $log->description,
                                'event' => $log->event,
                                'created_at' => $log->created_at->tz(session('timezone'))->format('M d, Y h:i A'),
                                'created_by' => User::find($log->causer_id)->name,
                                'data' => $nextColumnValue,
                            ];
                        });
        return inertia('Logs/Index', [
            'title' => 'Activity Logs',
            'logs' => $logs
        ]);
    }

    public function destroy(Activity $log)
    {
        // Gate::authorize('delete', $product_category);
        $log->delete();
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        // Gate::authorize('bulk_delete', ProductCategory::class);

        Activity::whereIn('id',$request->logs_id)->delete();
        return redirect()->back();
    }

   
}
