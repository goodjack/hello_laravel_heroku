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

    public function callback()
    {
        require_once(base_path('/vendor/linecorp/line-bot-sdk/line-bot-sdk-tiny/LINEBotTiny.php'));
        // $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('ChannelAccessToken'));
        // $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => env('ChannelSecret')]);

        // $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
        // $response = $bot->pushMessage('5859976114168', $textMessageBuilder);

        // $result = $response->getHTTPStatus() . ' ' . $response->getRawBody();

        $channelAccessToken = env('ChannelAccessToken');
        $channelSecret = env('ChannelSecret');

        $client = new LINEBotTiny($channelAccessToken, $channelSecret);
        foreach ($client->parseEvents() as $event) {
            switch ($event['type']) {
                case 'message':
                    $message = $event['message'];
                    switch ($message['type']) {
                        case 'text':
                            $client->replyMessage(array(
                                'replyToken' => $event['replyToken'],
                                'messages' => array(
                                    array(
                                        'type' => 'text',
                                        'text' => $message['text']
                                    )
                                )
                            ));
                            break;
                        default:
                            error_log("Unsupporeted message type: " . $message['type']);
                            break;
                    }
                    break;
                default:
                    error_log("Unsupporeted event type: " . $event['type']);
                    break;
            }
        };

        // return view('line.callback', compact('result'));
        // return view('line.callback');
    }
}
