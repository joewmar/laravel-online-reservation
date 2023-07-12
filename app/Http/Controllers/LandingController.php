<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class LandingController extends Controller
{
    public function teleUpdates(){
        // $updates = Telegram::getUpdates();
        // // dd($updates);
        Telegram::sendMessage([
            'chat_id' => getChatIdByUsername('joewmar'),
            'parse_mode' => 'HTML',
            'text' => 'Hello panis ka',
        ]);


    }
}
