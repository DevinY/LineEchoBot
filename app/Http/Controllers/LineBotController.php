<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Log;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\Event\UnfollowEvent;
use LINE\LINEBot\Event\UnknownEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;
use LINE\LINEBot\Event\MessageEvent\UnknownMessage;
use LINE\LINEBot\Event\MessageEvent\BaseEvent;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;

use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;

use App\Controllers\LineTemplate;
use App\Controllers\TextMessageEventHandler;
use App\Controllers\StickerMessageEventHandler;
use App\Controllers\ImageMessageEventHandler;
use GuzzleHttp\Client;


class LineBotController extends Controller
{
  private $bot;
  private $userId;
  private $roomId;
  private $groupId;
  private $lineSourceType;
  private $displayName;
  private $pictureUrl;

  public function __invoke(ServerRequestInterface $req){
      
    $signature = $req->getHeader('X-Line-Signature');
    if (empty($signature)) {
      return $res->withStatus(400, 'Bad Request');
    }
    $this->bot = resolve('LINE\LINEBot');
    Log::info("Get Request");
    // Check request with signature and parse request
    try {
        $events = $this->bot->parseEventRequest($req->getBody(), $signature[0]);
    } catch (InvalidSignatureException $e) {
      return $req->withStatus(400, 'Invalid signature');
    } catch (InvalidEventRequestException $e) {
      return $req->withStatus(400, "Invalid event request");
    }

    foreach ($events as $event) {

        //接收到圖檔訊訊
        if($event instanceof MessageEvent) {
          Log::info('MessageEvent');
          if ($event instanceof TextMessage) {
            Log::info("LineBotController.php TextMessage");
            //Laravel的Log中，可以看到RootId或GroupId，可以用來推播。
            // /api/text?to=[groupId]&message=推播
            Log::info($req->getBody());
            $handler = new TextMessageEventHandler($this->bot, $req, $event);
          }elseif($event instanceof ImageMessage){
            Log::info('ImageMessage');
            $handler = new ImageMessageEventHandler($this->bot, $req, $event);
          }elseif($event instanceof LocationMessage){
              Log::info('LocationMessage');
          }elseif($event instanceof AudioMessage){

          }elseif($event instanceof VideoMessage){

          }elseif($event instanceof StickerMessage ){
            $handler = new StickerMessageEventHandler($this->bot, $req, $event);
          }

        } elseif ($event instanceof UnfollowEvent) {

          } elseif ($event instanceof FollowEvent) {

          } elseif ($event instanceof JoinEvent) {

            Log::info("LineBotController.php JoinEvent");
            //Laravel的Log中，可以看到RootId或GroupId，可以用來推播。
            // /api/text?to=[groupId]&message=推播
            Log::info($req->getBody());
            return response('OK', 200)
            ->header('Content-Type', 'text/plain');

          } elseif ($event instanceof LeaveEvent) {

          } elseif ($event instanceof PostbackEvent) {
             $handler = new PostbackEventHandler($this->bot,$req, $event);
          } elseif ($event instanceof BeaconDetectionEvent) {

          } elseif ($event instanceof UnknownEvent) {

          } else {
            //無法預期的行為
            continue;
          }
    }

    $handler->handle();
    return response('OK', 200)
    ->header('Content-Type', 'text/plain');
  }

  //API測試push功能，取得一個短的toker
  public function getAccessToken()
  {
      $http = new Client;
      $postData =[
        'form_params' => [
        'grant_type' => 'client_credentials',
        'client_id' => env("LINEBOT_CHANNEL_ID"),
        'client_secret' => env("LINEBOT_CHANNEL_SECRET"),
        ],  
      ];
      //這一行跟OAUTH_SERVER要資料取得access token
      $response = $http->post('https://api.line.me/v2/oauth/accessToken', $postData ); 
      $response = json_decode((string) $response->getBody(), true);
      return $response['access_token'];
  }

}