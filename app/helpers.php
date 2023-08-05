<?php

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Reservation;
use Telegram\Bot\FileUpload\InputFile;
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
    $updatesBot1 = Telegram::getUpdates();
    $updatesBot2 = Telegram::bot('bot2')->getUpdates();
    $chat_id = null;
    foreach ($updatesBot1 as $update) {
        $message = $update->getMessage();
        $chat = $message->getChat();
        
        if ($chat->getUsername() === $username) {
            $chat_id['bot1'] = $chat->getId();
        }
        else{
            $chat_id['bot1'] = null;
        }
    }
    foreach ($updatesBot2 as $update) {
        $message = $update->getMessage();
        $chat = $message->getChat();

        if ($chat->getUsername() === $username) {
            $chat_id['bot2'] = $chat->getId();
        }
        else{
            $chat_id['bot2'] = null;
        }
    }
    if($chat_id['bot1'] == $chat_id['bot2']){
        $chat_id = $chat_id['bot1'];
    }
    return $chat_id;
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
function checkAvailRooms($pax, $dates){
    $isFull = false;
    $countPax = 0;
    $rooms = Room::all();
    $reservation = Reservation::where('status', '>', 1);
    $maxOccAll = 0;
    foreach($rooms as $key => $room){
        if($room->availability == 0){
            $countOccupancy = 0;
            $maxOccAll += $room->room->max_occupancy;
            if($room->customer){
                foreach ($room->customer as $key => $item) {
                    $arrPreCus[$key]['pax'] = $item ?? '';
                    if(!($room->room->max_occupancy >=  $countOccupancy)){
                        $countOccupancy += (int)$arrPreCus[$key]['pax'];
                    }
                }
            }
            $countPax += (int)$countOccupancy;
        }
    }
    $compute = $maxOccAll - $countPax ; // Get All Available Room
    if($compute >= $pax){
        foreach($reservation as $item){
            if(Carbon::parse($item->check_out)->timestamp < Carbon::parse($dates)->timestamp){
                $isFull = true;
                break;
            }
            else{
                $isFull = false;
    
            }
        }
    }
    else{
        $isFull = true;
    }
    unset($compute, $countPax, $arrPreCus, $countOccupancy, $maxOccAll, $rooms, $reservation, $countPax);
    return $isFull;
}
function telegramSendMessage($chatID, $message, $keyboard = null, $bot = 'bot1'){
    try{
        if($keyboard != null){
            Telegram::bot($bot)->sendMessage([
                'chat_id' => $chatID,
                'parse_mode' => 'HTML',
                'text' => $message,
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard]) ,
            ]);
        }
        else{
            Telegram::bot($bot)->sendMessage([
                'chat_id' => $chatID,
                'parse_mode' => 'HTML',
                'text' => $message,
            ]);
        }
    }
    catch(Exception $e){
        
    }
    
}
function telegramSendMessageWithPhoto($chatID, $message, $photoPath, $keyboard = null, $bot = 'bot1'){    
    try{
        if($keyboard !== null){
            Telegram::bot($bot)->sendPhoto([
                'chat_id' => $chatID,
                'photo' => InputFile::create($photoPath),
                'caption' => $message,
                'parse' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard]) ,
            ]);
        }
        else{
            Telegram::bot($bot)->sendPhoto([
                'chat_id' => $chatID,
                'parse' => 'HTML',
                'photo' => InputFile::create($photoPath),
                'caption' => $message,
            ]);
        }
    }
    catch(Exception $e){

    }
}