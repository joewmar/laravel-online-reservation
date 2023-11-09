<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\System;
use App\Models\AuditTrail;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    private $system_user;

    public function __construct()
    {
        $this->system_user = auth('system'); 
        $this->middleware(function ($request, $next){
            if(!($this->system_user->user()->type === 0)) abort(404);
            return $next($request);

        });
    }
    public function index(Request $request){
        $activities = new AuditTrail();
        $search = $request['name'];
        if($request->has('name')){
            // Retrieve the activity logs based on the full name
            $value = $activities->whereHas('employee', function ($query) use ($search ) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
            if($value->get()->isEmpty()){
                $activities = $activities->whereRaw("name LIKE ?", ["%$search%"]);
            }
            else{
                $activities = $activities->whereHas('employee', function ($query) use ($search ) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                });
            }
        }
        if($request->has('roles')){
            $activities = $activities->whereIn('role', $request['roles']);
        }

        if($request->has('start') && $request->has('end')){
            $activities = $activities->whereBetween('created_at', [$request['start'], $request['end']]);
        }
        elseif($request->has('now')){
            $activities = $activities->whereDate('created_at', '>=', $request['now'])->whereDate('created_at', '<=', $request['now']);
        }
        else{
            $dates = $activities->pluck('created_at');
            $sortedDates = $dates->sort();
            $firstDate = $sortedDates->first();
            $lastDate = $sortedDates->last();
            $dateRange = [
                'start_date' => $firstDate->format('F j, Y'),
                'end_date' => $lastDate->format('F j, Y'),
            ];
        }
        return view('system.setting.auditlogs.index',  ['activeSb' => 'Activity Log', 'activities' => $activities->paginate(10), 'roles' => [0 => 'Admin', 1 => 'Manager', 2 => 'Front Desk']]);
    }
    public function search(Request $request){
        $params = [];
        if(isset($request['name'])) $params['name'] = $request['name']; 
        if(isset($request['roles'])) $params['roles'] = decryptedArray($request['roles'] );


        if(isset($request['start']) && isset($request['end'])){
            $params['start'] = $request['start'];
            $params['end'] = $request['end'];
        }
        elseif(isset($request['now'])) $params['now']  = Carbon::now('Asia/Manila')->format('Y-m-d');
        
        if(isset($request['generate']) && $request['generate'] == 'true') return redirect()->route('system.setting.audit.report', Arr::query($params));
        else return redirect()->route('system.setting.audit.home', Arr::query($params));
    }
    public function report(Request $request){
        $dateRange = [];
        $activities = new AuditTrail();
        $search = $request['name'];

        if($request->has('name')){
            // Retrieve the activity logs based on the full name
            $value = $activities->whereHas('employee', function ($query) use ($search ) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
            if($value->get()->isEmpty()){
                $activities = $activities->whereRaw("name LIKE ?", ["%$search%"]);
            }
            else{
                $activities = $activities->whereHas('employee', function ($query) use ($search ) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                });
            }
        }
        if($request->has('roles')){
            $activities = $activities->whereIn('role', $request['roles']);
        }

        if($request->has('start') && $request->has('end')){
            $activities = $activities->whereBetween('created_at', [$request['start'], $request['end']]);

            $dateRange = [
                'start_date' => Carbon::createFromFormat('Y-m-d', $request['start'])->format('F j, Y'),
                'end_date' => Carbon::createFromFormat('Y-m-d', $request['end'])->format('F j, Y'),
            ];
        }
        elseif($request->has('now')){
            $activities = $activities->whereDate('created_at', '>=', $request['now'])->whereDate('created_at', '<=', $request['now']);
            $dateRange = [
                'now' => Carbon::createFromFormat('Y-m-d', $request['now'])->format('F j, Y'),
            ];
        }
        else{
            $dates = $activities->pluck('created_at');
            $sortedDates = $dates->sort();
            $firstDate = $sortedDates->first();
            $lastDate = $sortedDates->last();
            $dateRange = [
                'start_date' => $firstDate->format('F j, Y'),
                'end_date' => $lastDate->format('F j, Y'),
            ];
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml(view('system.setting.auditlogs.report', ['activities' => $activities->get(), 'dateRange' => $dateRange, 'roles' => [0 => 'Admin', 1 => 'Manager', 2 => 'Front Desk']]));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('Legal');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('activity-log-'.now()->format('YmdHis').'.pdf', ['Attachment' => false]);
    }
}
