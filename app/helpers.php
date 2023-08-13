<?php

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Telegram\Bot\FileUpload\InputFile;
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

    return (int)$date1->diffInDays($date2); // Calculate the number of days between the two dates
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
function getChatIdByUsername($username)
{
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
function checkAvailRooms($pax, $dates)
{
    $isFull = false;
    $countPaxes = 0;
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
            $countPaxes += (int)$countOccupancy;
        }
    }
    $vacantAll = abs($maxOccAll - $countPaxes) ; // Get All Available Room
    if($vacantAll >= $pax && $maxOccAll !== $countPaxes){
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
    unset($vacantAll, $countPaxes, $arrPreCus, $countOccupancy, $maxOccAll, $rooms, $reservation, $countPaxes);
    return $isFull;
}
function telegramSendMessage($chatID, $message, $keyboard = null, $bot = 'bot1')
{
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
function telegramSendMessageWithPhoto($chatID, $message, $photoPath, $keyboard = null, $bot = 'bot1')
{    
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
function saveImageWithJPG(Request $request, $fieldName, $folderName, $option = 'public')
{
    if($request->hasFile($fieldName)) {
        $imageFile = $request->file($fieldName);

        // Generate a unique filename for the image
        $imageName = uniqid() . '.jpg';

        // Generate the full path where you want to save the image in the storage folder
        $destinationPath =  $option. '/' . $folderName . '/' . $imageName;

        // Save the image using Intervention Image
        $image = Image::make($imageFile)->encode('jpg', 65);

        // Store the image in the storage folder
        Storage::put($destinationPath, (string)$image, $option);

        // Return the folder name and filename
        return $folderName . '/' . $imageName;
    }

    return null;
}