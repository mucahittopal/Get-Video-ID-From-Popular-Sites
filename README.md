# Get Video ID From Popular Sites With PHP
Let's take the ID information required to play with video links from popular video sites (Youtube, Google Drive, Facebook, Vimeo, Dailymotion, Ok.ru, Twitch), and create embed code and let you use the videos anywhere you want.

<a href="http://blablabla.mucahittopal.com/getVideoID.php" target="_blank">DEMO</a>

Let's take a look at our getVideoID function that I prepared directly without extending the word.

    function getVideoID($link){
      $isVideo    = false;
      $videoID    = "";
      $videoType  = "";
      if (!empty($link)) {
          if (preg_match('%(?:youtube(?:-nocookie)?.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu.be/)([^"&?/ ]{11})%i', $link, $match)) {
              $isVideo   = true;
              $videoID   = $match[1];
              $videoType = 'youtube';
  
        } else if(preg_match('/(?:https?://)?(?:[w-]+.)*(?:drive|docs).google.com/(?:(?:folderview|open|uc)?(?:[w-%]+=[w-%]*&)*id=|(?:folder|file|document|presentation)/d/|spreadsheet/ccc?(?:[w-%]+=[w-%]*&)*key=)([w-]{28,})/i', $link , $match)){
            $isVideo   = true;
            $videoID   = $match[1];
            $videoType = 'google';
 
        } else if (preg_match("#https?://vimeo.com/([0-9]+)#i", $link, $match)) {
            $isVideo   = true;
            $videoID   = $match[1];
            $videoType = 'vimeo';
 
        } else if (preg_match('#https?:.*?.(mp4|mov)#s', $link, $match)) {
            $isVideo   = true;
            $videoType = 'mp4';
            $videoID   = $match[0];
 
        }else if (preg_match('#https://www.dailymotion.com/video/([A-Za-z0-9]+)#s', $link, $match)) {
            $videoID   = $match[1];
            $videoType = 'daily';
            $isVideo   = true;
 
        } else if (preg_match('#(https://www.ok.ru/|https://ok.ru/)(video|live)/([A-Za-z0-9]+)#s', $link, $match)) {
            $videoID   = $match[3];
            $videoType = 'ok';
            $isVideo   = true;
 
        }else if (preg_match('@^(?:https?://)?(?:www.|go.)?twitch.tv(/videos/([A-Za-z0-9]+)|/([A-Za-z0-9]+)/clip/([A-Za-z0-9]+)|/(.*))($|?)@', $link, $match)) {
            $text = explode('/', $match[1]);
            if ($text[1] == 'videos') {
                $videoType      = 'twitch_videos';
                $videoID = $text[2];
                $isVideo  = true;
            }
            else if ($text[2] == 'clip') {
                $videoType      = 'twitch_clip';
                $videoID = $text[3];
                $isVideo  = true;
            }
            else if (!empty($text[1])){
                $videoType      = 'twitch_streams';
                $videoID = $text[1];
                $isVideo  = true;
            }
             
        }else if (preg_match('~([A-Za-z0-9]+)/videos/(?:t.d+/)?(d+)~i', $link, $match) ) {
            $videoID   = $match[0];
            $videoType = 'facebook';
            $isVideo   = true;
 
        }
    }
    return ["videoID"=>$videoID,"videoType"=>$videoType,"isVideo"=>$isVideo];
    }

We send the video link that we assign to our $link variable, which is very simple to use, into the getVideoID function and gives us the required ID information and which site it belongs to as the array output.

For example

    $link="https://www.youtube.com/watch?v=O8CCJKzj4BM";

    $video=getVideoID($link);

    print_r($video);

    /*
    Ekran çıktısı aşağıdaki gibidir

    Array
    (
        [videoID] => O8CCJKzj4BM
        [videoType] => youtube
        [isVideo] => 1
    )
    */
    
You can use the videoID provided by the function in your own player or embed code, or save it for later use.

Let's prepare the playerVideo function according to the output of our getVideoID function.

    function playVideo($params=[]){
        $data=[];
        $videoID   = $params["videoID"];
        $videoType = $params["videoType"];
        $isVideo   = $params["isVideo"];
        if(!$isVideo){
            $data["error"]= "Not found video";
        }

        if(empty($videoID)){
            $data["error"]= "Not found video id";
        }

        if(!isset($data["error"])){
            switch($videoType){
                case "youtube":
                $data["ok"]='<iframe src="https://www.youtube.com/embed/'.$videoID.'?playlist='.$videoID.'&enablejsapi=1&controls=0&fs=0&iv_load_policy=3&rel=0&showinfo=0&loop=1&autoplay=1" width="100%" height="100%" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>';
                break;

                case "google":
                $data["ok"]='<iframe width="100%" height="100%" src="https://drive.google.com/file/d/'.$videoID.'/preview" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>';
                break;

                case "vimeo":
                $data["ok"]='<iframe width="100%" height="100%" src="http://player.vimeo.com/video/'.$videoID.'?api=1;title=0&byline=0&portrait=0&autoplay=1" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>';
                break;

                case "mp4":
                $data["ok"]='<video controls=""><source src="'.$videoID.'" type="'.$videoType.'" data-quality="360p" title="360p" label="360p" res="360"></video>';
                break;

                case "daily":
                $data["ok"]='<iframe width="100%" height="100%" src="//www.dailymotion.com/embed/video/'.$videoID.'?PARAMS" frameborder="0" allowfullscreen=""></iframe>';
                break;

                case "ok":
                $data["ok"]='<iframe width="100%" height="100%" src="//ok.ru/videoembed/'.$videoID.'" allowfullscreen=""></iframe>';
                break;

                case "twitch_videos":
                $link = 'https://player.twitch.tv/?video='.$videoID;
                $data["ok"]='<iframe width="100%" height="100%" src="'.$link.'&autoplay=false" allowfullscreen="">';
                break;

                case "twitch_clip":
                $link = 'https://clips.twitch.tv/embed?clip='.$videoID;
                $data["ok"]='<iframe width="100%" height="100%" src="'.$link.'&autoplay=false" allowfullscreen>';
                break;

                case "twitch_streams":
                $data["ok"]='<script src= "https://player.twitch.tv/js/embed/v1.js"></script>
                    <div id="twitch_player"></div>
                        <script type="text/javascript">
                            var options = {
                              width: "100%",
                              channel: "'.$videoID.'",
                            };
                            var player = new Twitch.Player("twitch_player", options);
                         </script>';
                break;

                case "facebook":
                $data["ok"]='<iframe width="100%" height="100%" src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/'.urldecode($videoID).'&show_text=0&width=100" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>';
                break;

                default:
                $data["error"]= "Not found video type";
                break;
            }
        }
        return $data;
    }
    
Let's look at its use together.

    $link="https://www.youtube.com/watch?v=O8CCJKzj4BM";
 
    $video=getVideoID($link);

    $player=playVideo($video);

    print_r($player);

    /*
    Ekran çıktısı aşağıdaki gibidir

    Örnek başarılı çıktısı:

    Array
    (
        [ok] =><iframe src="https://www.youtube.com/embed/O8CCJKzj4BM?playlist=O8CCJKzj4BM&enablejsapi=1&controls=0&fs=0&iv_load_policy=3&rel=0&showinfo=0&loop=1&autoplay=1" width="100%" height="100%" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>
    )

    Örnek hata çıktısı:

    Array
    (
        [error] => Not found video id
    )
    */
    
If you get stuck, don't hesitate to ask. The sites that come to my mind for now, if you want to get this much, you can specify them in the comments on my site.

<a href="https://www.mucahittopal.com/php-ile-video-sitelerinden-id-alma-ve-oynatma-fonksiyonu.html" target="_blank">Topic on my site</a>
