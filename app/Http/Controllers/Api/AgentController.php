<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * Agent polls this for pending jobs.
     * GET /api/print-jobs/pending?storeId=...
     */
    public function getPendingJobs(Request $request)
    {
        $storeId = $request->query('storeId');
        if (!$storeId) {
            return response()->json(['error' => 'Store ID required'], 400);
        }

        // Get jobs with status 'pending'
        $jobs = PrintJob::where('store_id', $storeId)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        // Convert to the exact format the agent expects
        $formattedJobs = $jobs->map(fn($j) => $j->toAgentArray());

        // Update status to 'queued' to prevent double picking if polling overlaps
        PrintJob::whereIn('id', $jobs->pluck('id'))->update(['status' => 'queued']);

        return response()->json($formattedJobs);
    }

    /**
     * Agent sends messages/status updates here.
     * POST /api/agent/messages
     */
    public function handleMessage(Request $request)
    {
        $message = $request->json()->all();
        $storeId = $request->header('X-Store-Id');

        // Log the message for auditing
        Log::debug("Print Agent Message from Store {$storeId}: " . json_encode($message));

        // If it's a status update for a job
        if (isset($message['Type']) && $message['Type'] === 'job_status') {
            $jobId = $message['JobId'];
            $status = strtolower($message['Status'] ?? 'unknown');
            
            $dbJob = PrintJob::where('job_id', $jobId)->first();
            if ($dbJob) {
                $dbStatus = match($status) {
                    'queued'    => 'queued',
                    'rendering' => 'printing',
                    'printing'  => 'printing',
                    'completed' => 'printed',
                    'failed'    => 'failed',
                    default     => 'queued',
                };
                
                $dbJob->update([
                    'status'        => $dbStatus,
                    'error_message' => $message['ErrorMessage'] ?? null,
                    'printed_at'    => $dbStatus === 'printed' ? now() : $dbJob->printed_at,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Optional: Health check
     */
    public function health()
    {
        return response()->json(['status' => 'ok']);
    }
}
