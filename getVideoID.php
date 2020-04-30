<?php

if($_GET){
	$video=getVideoID($_GET["url"]);
	$play=playVideo($video);
	if (isset($play["ok"])) {
		echo $play["ok"];
	}else{
		echo "<h4>".$play["error"]."</h4>";
	}
}else{
	echo "<form method='get' style='text-align: center;'>
	<h3> Get Video</h3> 
	<p><input type='url' name='url' placeholder='Video URL' style='width:50%;' required></p> 
	<p><button type='submit'>GET</button></p>
	</form>";
}

function getVideoID($link){
	$isVideo    = false;
	$videoID    = "";
	$videoType  = "";
	if (!empty($link)) {
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $link, $match)) {
			$isVideo   = true;
			$videoID   = $match[1];
			$videoType = 'youtube';

		} else if(preg_match('/(?:https?:\/\/)?(?:[\w\-]+\.)*(?:drive|docs)\.google\.com\/(?:(?:folderview|open|uc)\?(?:[\w\-\%]+=[\w\-\%]*&)*id=|(?:folder|file|document|presentation)\/d\/|spreadsheet\/ccc\?(?:[\w\-\%]+=[\w\-\%]*&)*key=)([\w\-]{28,})/i', $link , $match)){
			$isVideo   = true;
			$videoID   = $match[1];
			$videoType = 'google';

		} else if (preg_match("#https?://vimeo.com/([0-9]+)#i", $link, $match)) {
			$isVideo   = true;
			$videoID   = $match[1];
			$videoType = 'vimeo';

		} else if (preg_match('#https?:.*?\.(mp4|mov)#s', $link, $match)) {
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

		}else if (preg_match('@^(?:https?:\/\/)?(?:www\.|go\.)?twitch\.tv(\/videos\/([A-Za-z0-9]+)|\/([A-Za-z0-9]+)\/clip\/([A-Za-z0-9]+)|\/(.*))($|\?)@', $link, $match)) {
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
			
		}else if (preg_match('~([A-Za-z0-9]+)/videos/(?:t\.\d+/)?(\d+)~i', $link, $match) ) {
			$videoID   = $match[0];
			$videoType = 'facebook';
			$isVideo   = true;

		}
	}
	return ["videoID"=>$videoID,"videoType"=>$videoType,"isVideo"=>$isVideo];
}

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
			$data["ok"]='<iframe src="https://www.youtube.com/embed/'.$videoID.'?playlist='.$videoID.'&enablejsapi=1&controls=0&fs=0&iv_load_policy=3&rel=0&showinfo=0&loop=1&autoplay=1" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
			break;
			
			case "google":
			$data["ok"]='<iframe width="100%" height="100%" src="https://drive.google.com/file/d/'.$videoID.'/preview" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
			break;
			
			case "vimeo":
			$data["ok"]='<iframe width="100%" height="100%" src="http://player.vimeo.com/video/'.$videoID.'?api=1;title=0&amp;byline=0&amp;portrait=0&amp;autoplay=1" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
			break;
			
			case "mp4":
			$data["ok"]='<video controls><source src="'.$videoID.'" type="'.$videoType.'" data-quality="360p" title="360p" label="360p" res="360"></video>';
			break;
			
			case "daily":
			$data["ok"]='<iframe width="100%" height="100%" src="//www.dailymotion.com/embed/video/'.$videoID.'?PARAMS" frameborder="0" allowfullscreen></iframe>';
			break;
			
			case "ok":
			$data["ok"]='<iframe width="100%" height="100%" src="//ok.ru/videoembed/'.$videoID.'" allowfullscreen></iframe>';
			break;
			
			case "twitch_videos":
			$link = 'https://player.twitch.tv/?video='.$videoID;
			$data["ok"]='<iframe width="100%" height="100%" src="'.$link.'&autoplay=false" allowfullscreen>';
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
