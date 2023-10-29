<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            $activities = $activities->whereHas('employee', function ($query) use ($search ) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if($request->has('roles')){
            $activities = $activities->whereHas('employee', function ($query) use ($request) {
                $query->whereIn('type', $request['roles']);
            });
        }
        if($request->has('start') && $request->has('end')){
            $activities = $activities->whereBetween('created_at', [$request['start'], $request['end']]);
        }
        elseif($request->has('now')){
            $activities = $activities->whereDate('created_at', '>=', $request['now'])->whereDate('created_at', '<=', $request['now']);
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
        
        return redirect()->route('system.setting.audit.home', Arr::query($params));
    }
}
