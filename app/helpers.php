<?php

use Carbon\Carbon;
use App\Models\Room;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;


function deleteFile($filename){
    if (Storage::disk('public')->exists($filename)) {
        Storage::disk('public')->delete($filename);
    }
}
// Reboot the number of room
function refreshRoomNumber(){
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
function checkAllArrayValue($array){
    $check = collect($array)->every(function ($value) {
        return $value === null || empty($value);
    });
    return $check;
}
function checkDiffDates($dt1, $dt2){
    $date1 = Carbon::parse($dt1); // Convert $date1 to Carbon object
    $date2 = Carbon::parse($dt2); // Convert $date2 to Carbon object

    return $date1->diffInDays($date2); // Calculate the number of days between the two dates
}
function getAllArraySpecificKey($array, $context) : Array{
    $paxParameters = [];
    foreach ($array as $key => $value) {
        if (strpos($key, $context) === 0) {
            $paxParameters[$key] = $value;
        }
    }
    return $paxParameters;
}
function encryptedArray($array){
    $var = array_map(function ($encryptValue) {
        return encrypt($encryptValue);
    }, $array);
    return $var;
}
function decryptedArray($array){
    $var = array_map(function ($decryptValue) {
        return decrypt($decryptValue);
    }, $array);
    return $var;
}
// Convert to user to chat_id
function getChatIdByUsername($username){
    $updates = Telegram::getUpdates();

    foreach ($updates as $update) {
        $message = $update->getMessage();
        $chat = $message->getChat();

        if ($chat->getUsername() === $username) {
            return $chat->getId();
        }
    }

    return null;
}

function getChatIdByGroup(){
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

