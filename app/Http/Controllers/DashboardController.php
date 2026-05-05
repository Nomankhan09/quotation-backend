<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Category;
use App\Models\Quotation;
use App\Models\Task;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        $userId = auth()->id();

        // weeks
        $startOfCurrentWeek = Carbon::now()->startOfWeek();
        $endOfCurrentWeek   = Carbon::now()->endOfWeek();

        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek   = Carbon::now()->subWeek()->endOfWeek();

        $totalLeads      = Lead::where('user_id', $userId)->count();
        $totalProducts   = Product::where('user_id', $userId)->count();
        $totalCategories = Category::where('user_id', $userId)->count();
        $totalConversions = Quotation::where('user_id', $userId)->where('status', 'sent')->count();

        $recentLeads = Lead::where('user_id', $userId)
            ->latest()
            ->take(3)
            ->get(['id', 'full_name as name', 'company_name as company', 'email']);

        // top 3 today tasks
        $recentTasks = Task::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->limit(3)->get();

        return response()->json([
            'total_leads'      => $totalLeads,
            'total_products'   => $totalProducts,
            'total_categories' => $totalCategories,
            'recent_leads'     => $recentLeads,
            'total_conversions' => $totalConversions,
            'recent_tasks' => $recentTasks,
        ]);
    }
}
