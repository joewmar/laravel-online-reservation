<?php

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;


function deleteFile($filename, $option = 'public')
{
    $isExist = false;
    if (Storage::disk($option)->exists($filename)) {
        Storage::disk($option)->delete($filename);
        $isExist = true;
    }
    return $isExist;
}
// Reboot the number of room
function refreshRoomNumber()
{
    // Update to blank
    $rooms = Room::all(); // Retrieve all the rooms
    if($rooms){
        $newRoomNo = 1;
        foreach ($rooms as $room) {
            // Perform your logic to update the room_no field here
        
            $room->room_no = $newRoomNo;
            $room->save(); // Save the updated room_no value
            $newRoomNo++;
        }
    }
}
function getNoDays($dt1, $dt2)
{
    $date1 = Carbon::parse($dt1); // Convert $date1 to Carbon object
    $date2 = Carbon::parse($dt2); // Convert $date2 to Carbon object
    if($date1->timestamp === $date2->timestamp) return 1;
    else return (int)$date1->diffInDays($date2) + 1 ; // Calculate the number of days between the two dates
}
function encryptedArray($array)
{
    $var = array_map(function ($encryptValue) {
        return encrypt($encryptValue);
    }, $array);
    return $var;
}
function decryptedArray($array)
{
    $var = array_map(function ($decryptValue) {
        return decrypt($decryptValue);
    }, $array);
    return $var;
}
// Convert to user to chat_id
function getChatIdByUsername($username, $bot = 'bot1')
{
    $updatesBot = Telegram::bot($bot)->getUpdates();
    $chat_id = null;
    foreach ($updatesBot as $update) {
        $message = $update->getMessage();
        $chat = $message->getChat();

        if ($chat->getUsername() === $username) {
            $chat_id = $chat->getId();
        }
    }
    return $chat_id;
}

function getChatIdByGroup()
{
    $updates = Telegram::getUpdates();

    foreach ($updates as $update) {
        $message = $update->getMessage();
        $chat = $message->getChat();

        if ($chat->getType() === 'group') {
            return $chat->getId();
        }
    }
    return null;
}
function checkAvailRooms(int $pax, $check_in, $check_out, $id = null)
{
    $isFull = false;
    $rooms = Room::all();
    $count_paxes = 0;
    $countMaxPaxes = 0;

    $r_lists = Reservation::where(function ($query) use ($check_in, $check_out) {
        $query->whereBetween('check_in', [$check_in, $check_out])
                ->orWhereBetween('check_out', [$check_in, $check_out])
                ->orWhere(function ($query) use ($check_in, $check_out) {
                    $query->where('check_in', '<=', $check_in)
                        ->where('check_out', '>=', $check_out);
                });
    })->whereBetween('status', [1, 2]);
    if(isset($id)) $r_lists = $r_lists->whereNot('id', $id)->pluck('id');
    else $r_lists = $r_lists->pluck('id');

    $rooms = Room::all();
     foreach($rooms as $room) {
        $customer = $room->customer;
        foreach($r_lists as $r_list){
            if(array_key_exists($r_list, $customer ?? [])){
                $count_paxes += $room->customer[$r_list] ?? 0;
            }
        }

        $countMaxPaxes += $room->room->max_occupancy;

    }
    if($count_paxes > $countMaxPaxes) $isFull = true;
    else $isFull = false;
    
    // dd($isFull);
    return $isFull;

}

function saveImageWithJPG(Request $request, $fieldName, $folderName,$option = 'public', $specificKey = null)
{   
    $savedImagePaths = null; // To store paths of saved images

    if ($request->hasFile($fieldName)) {
        if(isset($specificKey) && is_array($request->file($fieldName))) $imageFile = $request->file($fieldName)[$specificKey];
        else $imageFile = $request->file($fieldName);
        
        // Generate a unique filename for the image
        $imageName = Str::random(4). now()->format('YmdHis') . '.jpg';
        // Generate the full path where you want to save the image in the storage folder
        $destinationPath = $option . '/' . $folderName . '/' . $imageName;
        // Save the image using Intervention Image
        $image = Image::make($imageFile)->encode('jpg', 65);
        // Store the image in the storage folder
        Storage::put($destinationPath, (string)$image, $option);
        // Store the saved image path
        $savedImagePaths = $folderName . '/' . $imageName;
        return $savedImagePaths;
    }
    return null;
}