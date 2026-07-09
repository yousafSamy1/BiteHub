<?php

namespace App\Http\Controllers;

use App\Models\ErrorReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ErrorReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
            'error_code' => 'required|string',
        ]);

        \Log::info('Error report received: ' . json_encode($request->all()));

        ErrorReport::create([
            'UserID' => Auth::id(),
            'URL' => $request->url,
            'ErrorCode' => $request->error_code,
            'UserAgent' => $request->header('User-Agent'),
            'Status' => 'Pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Report sent successfully!']);
    }

    public function index()
    {
        // Admin index will be added later or integrated into AdminController
    }
}
