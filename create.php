<?php

	$results = file_get_contents('text.txt');
	$wp_comments = eval("return " . $results . ";");

	function post_to_url($url, $data) {
		   $fields = '';
		   foreach($data as $key => $value) { 
		      $fields .= $key . '=' . $value . '&'; 
		   }
		   rtrim($fields, '&');

		   $post = curl_init();

		   curl_setopt($post, CURLOPT_URL, $url);
		   curl_setopt($post, CURLOPT_POST, count($data));
		   curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
		   curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

		   $result = curl_exec($post);

		   curl_close($post);
		   return $result;
	}
	
	if(isset($_POST['submit'])) {

		$text = $_POST['something'];
		$pass = $_POST['nothing'];

		if ($pass == 'YOURSUPERSECRETPASSWORDHERE'){
			reset($wp_comments);
			if ($results == FALSE){
				$itemID = 1	
			}
			else{
				$item = array_values($wp_comments)[0];
				$itemID = intval($item['comment_ID']);
				$itemID++;	
			}
			$comment_id = strval($itemID);
			
			// Change the line below to your timezone!
			date_default_timezone_set('YOURTIMEZONEHERE'); // Mine's America/Denver
			$date = date('Y-m-d H:i:s', time());

			$postarray = array(
				'comment_author' => 'YOURNAMEHERE',
				'comment_date' => $date,
				'comment_content' => $text,
				'comment_ID' => $comment_id
			);

			array_unshift($wp_comments, $postarray);
			$result = var_export($wp_comments, true); 
			file_put_contents('text.txt', $result);

			if (strlen($text) > 256){
				$ADNURL = 'http://YOURLIVEBLOGHERE.COM#'.$comment_id;
				$ADNtext = substr($text, 0, 190);
				$ADNtext = $ADNtext.'... '.$ADNURL;
			}
			else{
				$ADNtext = $text;
			}

			if (strlen($text) > 140){
				$TwURL = 'http://YOURLIVEBLOGHERE.COM#'.$comment_id;
				$Twtext = substr($text, 0, 75);
				$Twtext = $Twtext.'... '.$TwURL;
			}
			else{
				$Twtext = $text;
			}

			$ADNtext = str_replace("\'", "'", $ADNtext);
			$ADNtext = str_replace("\&quot;", "\"", $ADNtext);
			$ADNtext = urlencode($ADNtext);

			$data = array(
			   "text" => $ADNtext,
			   "access_token" => "ADNACCESSTOKENHERE"
			);

			$the_result = post_to_url('https://alpha-api.app.net/stream/0/posts',$data);
			
			$the_result = preg_replace( "/\n/", "", $the_result);
			$the_Array = json_decode($the_result,true);

			require_once('codebird.php');
			 
			\Codebird\Codebird::setConsumerKey("TWITTERAPIKEY", "TWITTERAPISECRET");
			$cb = \Codebird\Codebird::getInstance();
			$cb->setToken("TWITTERUSERTOKEN", "TWITTERUSERTOKENSECRET");
			 
			$params = array(
			  'status' => $Twtext
			);
			$reply = $cb->statuses_update($params);
			echo $reply;
		}
	}
?>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/js/bootstrap.min.js"></script>
<script>
	function textareaLengthCheck() {
		tex = document.getElementById('something');
	    var length = tex.value.length;
	    // var charactersLeft = 500 - length;
	    var count = document.getElementById('count');
	    count.innerHTML = length + " chars.";
	}
</script>
<form method="post" action="" class="form-horizontal">
    <textarea onkeyup="textareaLengthCheck()" rows="7" cols="50" id="something" name="something" value="<?= isset($_POST['something']) ? htmlspecialchars($_POST['something']) : '' ?>" ></textarea><br />
    <textarea rows="1" cols="20" id="nothing" name="nothing" value="<?= isset($_POST['nothing']) ? htmlspecialchars($_POST['nothing']) : '' ?>" ></textarea><br />
    <input type="submit" class="btn" name="submit" />
    <p id="count"></p>
</form>
