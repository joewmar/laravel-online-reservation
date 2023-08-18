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
        $sales = Archive::selectRaw('DATE(created_at) as daily, SUM(total) as total_amount')
        ->groupBy('daily')
        ->orderBy('daily')
        ->get()
        ->map(function ($item) {
            $formattedDate = Carbon::createFromFormat('Y-m-d', $item->daily)->format('M j, Y');
            $item->formatted_date = $formattedDate;
            return $item;
        });
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
        
                $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $endDate = Carbon::now()->setISODate($year, $week)->endOfWeek();
        
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
        $nationalities = Archive::groupBy('nationality')->selectRaw('nationality, count(*) as count')->get();
        return view('system.analytics.index', ['activeSb' => 'Analytics', 'sales' => $sales ?? [], 'nationalities' => $nationalities]);
    }
}
