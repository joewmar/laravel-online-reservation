<?php

namespace App\Console\Commands;

use App\Models\Addons;
use App\Models\TourMenu;
use App\Models\Reservation;
use App\Models\RoomRate;
use Illuminate\Console\Command;

class SoftDeleteCommand extends Command
{
    private function softDeletedID($type)
    {
        $ids = [];
        foreach(Reservation::all() ?? [] as $list){
            $trans = $list->transaction ?? [];
            foreach($trans as $key => $item){
                if(strpos($key, $type) !== false){
                    $id = str_replace($type, '', $key);
                    if(!in_array($id, $ids)){
                        $ids[] = $id;
                    }
                }
            }
        }
        return $ids;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:soft-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Informations that can force delete';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach(TourMenu::onlyTrashed()->get() ?? [] as $menu){
            $existsIDs = $this->softDeletedID('tm');
            if(!empty($existsIDs) && !in_array($menu->id, $existsIDs)){
                $menu->forceDelete(); 
            }
            $existsIDs = $this->softDeletedID('TA');
            if(!empty($existsIDs) && !in_array($menu->id, $existsIDs)){
                $menu->forceDelete(); 
            }
        }
        foreach(Addons::onlyTrashed()->get() ?? [] as $addon){
            $existsIDs = $this->softDeletedID('OA');
            if(!empty($existsIDs) && !in_array($addon->id, $existsIDs)){
                $addon->forceDelete(); 
            }
        }
        foreach(RoomRate::onlyTrashed()->get() ?? [] as $rate){
            $existsIDs = $this->softDeletedID('rid');
            if(!empty($existsIDs) && !in_array($rate->id, $existsIDs)){
                $rate->forceDelete(); 
            }
        }
        $this->info('Soft Delete Updated');
    }
}
