<?php

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Support\Str;
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
function checkAvailRooms(int $pax, $check_in, $check_out)
{
    $isFull = false;
    $rooms = Room::all();
    $vacantAll = 0;
    if(Room::checkAllAvailable()){
        foreach($rooms as $value){
            $vacantAll += $value->getVacantPax();
        }
    }
    else{
        $r_lists = Reservation::whereBetween('check_in', [$check_in, $check_out])->orWhereBetween('check_out', [$check_in, $check_out])->pluck('id');

        $count_paxes = 0;
        foreach($r_lists as $r_list){
            $rooms = Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->get();
            // dd($rooms);
            foreach($rooms as $room) {
                $count_paxes += $room->customer[$r_list] ?? 0;
                if($count_paxes > $room->room->max_occupancy) $isFull = true;
                else $isFull = false;
            }
        }
        
    }
    $isFull = !($vacantAll >= $pax);
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
function saveImageWithJPG(Request $request, $fieldName, $folderName,$option = 'public', $specificKey = null)
{   
    $savedImagePaths = null; // To store paths of saved images

    if ($request->hasFile($fieldName)) {
        if(isset($specificKey) && is_array($request->file($fieldName))) $imageFile = $request->file($fieldName)[$specificKey];
        else $imageFile = $request->file($fieldName);
        
        // Generate a unique filename for the image
        $imageName = Str::random(8) . '.jpg';
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