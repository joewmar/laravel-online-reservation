<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Contracts\Service\Attribute\Required;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = Carbon::today()->toDateString();
        if(auth('system')->user()->type == 0){
            $sales = Archive::selectRaw('DATE(created_at) as daily, SUM(total) as total_amount')
                ->groupBy('daily')
                ->orderBy('daily')
                ->get()
                ->map(function ($item) {
                    $formattedDate = Carbon::createFromFormat('Y-m-d', $item->daily)->format('M j, Y');
                    $item->formatted_date = $formattedDate;
                    return $item;
                });
            $customerCount = Archive::selectRaw('DATE(created_at) as daily, COUNT(DISTINCT id) as customer_count')
                ->groupBy('daily')
                ->orderBy('daily')
                ->get()
                ->map(function ($item) {
                    $formattedDate = Carbon::createFromFormat('Y-m-d', $item->daily)->format('M j, Y');
                    $item->formatted_date = $formattedDate;
                    return $item;
                });
        }
        else{
            $sales = Archive::selectRaw('DATE(created_at) as daily, SUM(total) as total_amount')
                ->whereDate('created_at', $currentDate)
                ->groupBy('daily')
                ->orderBy('daily')
                ->get()
                ->map(function ($item) {
                    $formattedDate = Carbon::createFromFormat('Y-m-d', $item->daily)->format('M j, Y');
                    $item->formatted_date = $formattedDate;
                    return $item;
            });
            $customerCount = Archive::selectRaw('DATE(created_at) as daily, COUNT(DISTINCT id) as customer_count')
                ->whereDate('created_at', $currentDate)
                ->groupBy('daily')
                ->orderBy('daily')
                ->get()
                ->map(function ($item) {
                    $formattedDate = Carbon::createFromFormat('Y-m-d', $item->daily)->format('M j, Y');
                    $item->formatted_date = $formattedDate;
                    return $item;
                });
        }
        if($request->has('type') && $request['type'] === "sales" && auth('system')->user()->type == 0){
            if($request->has('tab') && $request['tab'] === "weekly" ){
                $sales=  DB::table('archives')
                ->selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, SUM(total) as total_amount')
                ->groupBy('year', 'week')
                ->orderBy('year', 'asc')
                ->orderBy('week', 'asc')
                ->get()
                ->map(function ($item) {
                    $year = $item->year;
                    $week = $item->week;
            
                    $startDate = Carbon::now('Asia/Manila')->setISODate($year, $week)->startOfWeek();
                    $endDate = Carbon::now('Asia/Manila')->setISODate($year, $week)->endOfWeek();
            
                    $formattedDateRange = $startDate->format('M j');
                    if ($startDate->month != $endDate->month) {
                        $formattedDateRange .= ' to ' . $endDate->format('M j, Y');
                    } else {
                        $formattedDateRange .= ' to ' . $endDate->format('j, Y');
                    }
    
                    $item->formatted_date_range = $formattedDateRange;
                    return $item;
                });
            }
            if($request->has('tab') && $request['tab'] === "monthly" ){
                $sales = Archive::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as total_amount')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
            }
            if($request->has('tab') && $request['tab'] === "yearly" ){
                $sales = Archive::selectRaw('YEAR(created_at) as year, SUM(total) as total_amount')
                ->groupBy('year')
                ->orderBy('year', 'asc')
                ->get();
            }
        }
        if($request->has('type') && $request['type'] === "customer" && auth('system')->user()->type == 0){
            if ($request->has('tab') && $request['tab'] === "weekly") {
                $customerCount = DB::table('archives')
                    ->selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, COUNT(DISTINCT id) as customer_count')
                    ->groupBy('year', 'week')
                    ->orderBy('year', 'asc')
                    ->orderBy('week', 'asc')
                    ->get()
                    ->map(function ($item) {
                        $formattedDate = Carbon::now()->setISODate($item->year, $item->week)->startOfWeek()->format('M j, Y') . ' - ' . Carbon::now()->setISODate($item->year, $item->week)->endOfWeek()->format('M j, Y');
                        $item->formatted_date = $formattedDate;
                        return $item;
                    });
            }

            if ($request->has('tab') && $request['tab'] === "monthly") {
                $customerCount = Archive::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(DISTINCT id) as customer_count')
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
                    ->get()
                    ->map(function ($item) {
                        $formattedDate = Carbon::create($item->year, $item->month, 1)->format('M j, Y');
                        $item->formatted_date = $formattedDate;
                        return $item;
                    });
            }

            if ($request->has('tab') && $request['tab'] === "yearly") {
                $customerCount = Archive::selectRaw('YEAR(created_at) as year, COUNT(DISTINCT id) as customer_count')
                    ->groupBy('year')
                    ->orderBy('year', 'asc')
                    ->get()
                    ->map(function ($item) {
                        $formattedYear = Carbon::create($item->year, 1, 1)->format('Y');
                        $item->formatted_year = $formattedYear;
                        return $item;
                    });
            }

        }

        $nationalities = Archive::groupBy('nationality')->selectRaw('nationality, count(*) as count')->get();
        return view('system.analytics.index', ['activeSb' => 'Analytics', 'sales' => $sales ?? [], 'nationalities' => $nationalities, 'customerCount' => $customerCount]);
    }
}
