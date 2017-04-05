<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

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
        $httpClient = new CurlHTTPClient(env('ChannelAccessToken'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('ChannelSecret')]);

        $events = $request->events;
        $log = [];
        $pushMessage = null;

        foreach ($events as $event) {
            $log['event_type'] = $event['type'];

            switch ($event['type']) {
                case 'message':
                    $log['reply_token'] = array_get($event, 'replyToken', null);
                    $log['message_id'] = $event['message']['id'];
                    $log['message_type'] = $event['message']['type'];

                    switch ($event['message']['type']) {
                        case 'text':
                            $log['message_text'] = $event['message']['text'];
                            // $pushMessage = "你剛剛是不是說" . $log['message_text'];
                            $chatbotResult = $this->sendRequest('GET', env('CHATBOT_API_URL') . rawurlencode($log['message_text']), [], []);
                            $pushMessage = print_r(json_decode($chatbotResult, true)['respSentence'], true);
                            break;
                        case 'image':
                            $pushMessage = "你傳什麼圖片啦，我又看不懂QQ";
                            break;
                        case 'video':
                            $pushMessage = "不要浪費流量看影片好嗎";
                            break;
                        case 'audio':
                            $pushMessage = "乖乖打字啦，外面很吵聽不到";
                            break;
                        case 'location':
                            $log['message_title'] = $event['message']['title'];
                            $log['message_address'] = $event['message']['address'];
                            $log['message_latitude'] = $event['message']['latitude'];
                            $log['message_longitude'] = $event['message']['longitude'];
                            $pushMessage = "這是哪裡？";
                            break;
                        case 'sticker':
                            $log['message_package_id'] = $event['message']['packageId'];
                            $log['message_sticker_id'] = $event['message']['stickerId'];
                            $pushMessage = "這啥貼圖？";
                            break;
                        default:
                            break;
                    }
                    break;
                case 'follow':
                    $pushMessage = "嗨嗨嗨，你來追蹤啦～～";
                    break;
                case 'unfollow':

                    break;
                case 'join':
                    $pushMessage = "這是什麼群組？";
                    break;
                case 'leave':

                    break;
                case 'postback':
                    $log['postback_data'] = $event['postback']['data'];
                    break;
                case 'beacon':
                    $log['beacon_hwid'] = $event['beacon']['hwid'];
                    $log['beacon_type'] = $event['beacon']['type'];
                    break;
                default:

                    break;
            }

            $log['source_type'] = $event['source']['type'];

            switch ($event['source']['type']) {
                case 'user':
                    $log['user_id'] = $event['source']['userId'];
                    $pushID = $log['user_id'];
                    break;
                case 'group':
                    $log['group_id'] = $event['source']['groupId'];
                    $pushID = $log['group_id'];
                    break;
                case 'room':
                    $log['room_id'] = $event['source']['roomId'];
                    $pushID = $log['room_id'];
                    break;
                default:
                    break;
            }

            Log::info([
                'event' => $log,
            ]);

            if (isset($log['user_id']) || isset($pushMessage)) {
                // $textMessageBuilder = new TextMessageBuilder("以下是我收到的訊息～\n" . print_r($log, true));
                $textMessageBuilder = new TextMessageBuilder($pushMessage);
                $response = $bot->pushMessage($pushID, $textMessageBuilder);
            }

            unset($log);
            $pushMessage = null;
        }

        // $result = $response->getHTTPStatus() . ' ' . $response->getRawBody();

        // return view('line.callback', compact('result'));
        // return view('line.callback');
        // return response($response->getRawBody(), $response->getHTTPStatus());
        return response('OK', 200);
    }

    /**
     * Inspired by LINE
     * @param  string $method
     * @param  string $url
     * @param  array  $additionalHeader
     * @param  array  $reqBody
     * @return array
     */
    private function sendRequest($method, $url, array $additionalHeader, array $reqBody)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, env('USER_AGENT'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);

            if (empty($reqBody)) {
                // Rel: https://github.com/line/line-bot-sdk-php/issues/35
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: 0'));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reqBody));
            }
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
