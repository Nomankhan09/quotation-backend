<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Quotation;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $dealQuery = Deal::where('user_id', $userId);
        $totalDeals = (clone $dealQuery)->whereNotIn('stage_id', [6, 7])->count();
        $wonDeals = (clone $dealQuery)->where('stage_id', 6)->count();
        $totalRevenue = Quotation::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('quotations')
                ->groupBy('deal_id');
        })
            ->whereHas('deal', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('stage_id', 6);
            })
            ->sum('total_amount');

        $recentLeads = Lead::where('user_id', $userId)
            ->latest()
            ->take(3)
            ->get(['id', 'full_name as name', 'company_name as company', 'email']);

        // top 3 today tasks
        $recentTasks = Task::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->limit(3)->get();

        // pipeline overview
        $latestQuotationSubquery = DB::table('quotations as q1')
            ->select('q1.deal_id', 'q1.total_amount')
            ->join(
                DB::raw('(
            SELECT deal_id, MAX(id) as max_id
            FROM quotations
            GROUP BY deal_id
        ) as q2'),
                function ($join) {
                    $join->on('q1.id', '=', 'q2.max_id');
                }
            );
        $pipelineOverview = DB::table('deals')
            ->join('deal_stages', 'deals.stage_id', '=', 'deal_stages.id')
            ->leftJoinSub(
                $latestQuotationSubquery,
                'latest_quotes',
                function ($join) {
                    $join->on('deals.id', '=', 'latest_quotes.deal_id');
                }
            )->where('deals.user_id', $userId)
            ->select(
                'deal_stages.id',
                'deal_stages.stage_name as name',
                'deal_stages.color',
                DB::raw('COUNT(deals.id) as total_deals'),
                DB::raw('COALESCE(SUM(latest_quotes.total_amount), 0) as total_revenue')
            )->groupBy(
                'deal_stages.id',
                'deal_stages.stage_name',
                'deal_stages.color'
            )->orderBy('deal_stages.id')
            ->get();

        return response()->json([
            'total_leads'      => $totalLeads,
            'total_products'   => $totalProducts,
            'total_categories' => $totalCategories,
            'recent_leads'     => $recentLeads,
            'total_conversions' => $totalConversions,
            'recent_tasks' => $recentTasks,
            'active_deals' => $totalDeals,
            'won_deals' => $wonDeals,
            'total_revenue' => $totalRevenue,
            'pipeline_overview' => $pipelineOverview,
        ]);
    }
}
