<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \LINE\LINEBot;
use LINEBotTiny;

class LineController extends Controller
{
    public function index()
    {
        $ChannelAccessToken = env('ChannelAccessToken');
        $ChannelSecret = env('ChannelSecret');

        return view('line.index', compact(['ChannelAccessToken', 'ChannelSecret']));
    }

    public function callback(Request $request)
    {
        // $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('ChannelAccessToken'));
        // $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => env('ChannelSecret')]);

        // $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
        // $response = $bot->pushMessage('5859976114168', $textMessageBuilder);

        // $result = $response->getHTTPStatus() . ' ' . $response->getRawBody();

        // return view('line.callback', compact('result'));
        // return view('line.callback');
        return response('OK', 200);
    }
}
