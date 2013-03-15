<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once "src/facebook.php";


$app_id = "253204234752974";
$app_secret = "9b12db97de75600be77e679e5d7ef7a6";
$canvas_page = "https://royalepromotions.ca/facebook/imageupload/";
$album_id = '472992669421000'; // Get the first one. Shouldn't be empty!
$fanpage = '237445739630557';
$fanpage2 = '183866985000238';

$facebook = new Facebook(array(
  'appId'  => $app_id,
  'secret' => $app_secret,
  'fileUpload' => true
));


/*---------- SEND BACK TO APP START ------------*/ 
if (isset($_REQUEST["code"]))
{
    header("Location: http://www.facebook.com/mmdevel/app_253204234752974");
    exit;
}
/*---------- SEND BACK TO APP END ------------*/ 

/*---------- AUTHENTICATE APP START ------------*/ 
//$auth_url = "http://www.facebook.com/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . urlencode($canvas_page) .  "&scope=manage_pages";
$auth_url = "http://www.facebook.com/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . urlencode($canvas_page);


$signed_request = $_REQUEST["signed_request"];
list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

if (empty($data["user_id"])) {
	echo "<script type='text/javascript'>top.location.href = '" . $auth_url . "';</script>";
	exit;
}
/*---------- AUTHENTICATE APP END ------------*/ 


echo "<h3>Signed Request</h3>";
var_dump($signed_request);


/*----------FACEBOOK OBJECT DISPLAY START ------------*/ 
echo "<h3>Facebook</h3>";
print_r($facebook);
echo "<br>";
/*----------FACEBOOK OBJECT DISPLAY END ------------*/ 


//CLEAR SESSION
//$_SESSION['access_token'] = "";
$_SESSION['access_token'] = "AAADmSbR3084BAP7188AtUivXsdlPdLazmugaJeeVvvAKyvn3Xl1M8DE1Iu8j7hVHQWZBVxz67Ac6FYqDzV6pS7BaF2s6ODTkpPU1jzQZDZD";

if($_SESSION['access_token'] == "" || $_SESSION['access_token'] == false){
	/*---------- ACCESS TOKEN DISPLAY START ------------*/ 
	$short_access_token = $facebook->getAccessToken();
	//$access_token = "AAADmSbR3084BABGI5w6eq8GlUsXcIe8OmyZCYPZBs5xpt2Afhl6RO346wRVDwwlACQUZBEqN6VeZC7vxDcYxIUKJXafNF1sG99LAKsMKmAZDZD&expires=5183968";
	
	//https://developers.facebook.com/tools/access_token/
	
	$long_access_token_auth = "https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&client_secret=".$app_secret."&grant_type=fb_exchange_token&fb_exchange_token=".$short_access_token;
	$long_access_token = getSslPage($long_access_token_auth);
	
	echo "<h3>Short Access token</h3>";
	var_dump($short_access_token);
	
	
	echo "<h3>Long Access token URL Request</h3>";
	var_dump($long_access_token_auth);
	echo "<br>";
	var_dump($long_access_token);
	
	$tokenLen = strlen($long_access_token);
	$tokenDatePos = strrpos($long_access_token, "&expires");
	$tokenStartPos = strrpos($long_access_token, "access_token=") +13;
	$tokenEndPos = $tokenLen - $tokenDatePos;
	
	echo "tokenLen = " . $tokenLen . "<br>";
	echo "tokenDatePos = " . $tokenDatePos . "<br>";
	echo "tokenStartPos = " . $tokenStartPos . "<br>";
	echo "tokenEndPos = " . $tokenEndPos . "<br>";
	
	if($tokenDatePos!=""){
		$_SESSION['access_token'] =  substr($long_access_token, $tokenStartPos, "-".$tokenEndPos);
	} else {
		$_SESSION['access_token'] =  substr($long_access_token, $tokenStartPos, $tokenLen);
	}
	/*---------- ACCESS TOKEN DISPLAY END ------------*/ 
} 

$long_access_token = $_SESSION['access_token'];
echo "<h3>Long Access token</h3>";
var_dump($long_access_token);


/*----------FACEBOOK GET USER OBJECT DISPLAY START ------------*/ 
$user = $facebook->getUser();
echo "<h3>user</h3>";
print_r($user);
/*----------FACEBOOK GET USER OBJECT DISPLAY END ------------*/ 


if (!empty($user)) {
	

	$params = array(
		'access_token' => $long_access_token
	);
	 
	 
	echo "<h3>Fanpage ID</h3><a href='http://www.facebook.com/mmdevel/'>http://www.facebook.com/mmdevel/</a><br>";
	var_dump($fanpage);
	
	$accounts = $facebook->api('/me/accounts', 'GET', $params);
	 
	echo "<h3>Accounts</h3>";
	print_r($accounts);
	
	echo "<h3>Data</h3>"; 
	$data = $accounts['data'];
	var_dump($data);
	
	echo "<ul>";
	foreach($data as $account) {
	 
			echo "<li>". var_dump($account) . "</li>";
			
			if( $account['id'] == $fanpage2 || $account['name'] == $fanpage2 )
	 
					$fanpage_token = $account['access_token'];
	 
	}
	echo "</ul>";
	
	// Get all albums from the page
	// Must use app access token, not page token!
	// You can also use a static album id to test
	 
	$fanpage_albums = $facebook->api($fanpage . '/albums', 'GET', $params);
	
	echo "<h3>Albums</h3>";
	print_r($fanpage_albums);
	
	 
	$albums = $fanpage_albums['data'];
	 
	$sorted = array();
	echo "<ul>";
	 
	foreach($albums as $album) {
			echo "<li>". var_dump($album) . "</li>";
			if( ! strpos($album['name'], 'Test Album') )
	 
					continue;
	 
			$sorted[] = $album;
	 
	}
	echo "</ul>";
	
	
	
	
	echo "<h3>album_id  </h3><a href='http://www.facebook.com/media/set/?set=a.472992669421000.1073741825.183866985000238&type=3'>http://www.facebook.com/media/set/?set=a.472992669421000.1073741825.183866985000238&type=3</a><br>";
	var_dump($album_id);
	 
	 
	 
	// Upload the photo (previously uploaded by user)
	 
	$args = array(
	 
			'message' => 'Von das scheirber.',
			'image' => '@'.realpath('cupping.jpg'),
			'aid' => $album_id,
			'no_story' => 1, // Nicht auf der Wall anzeigen (Thank God for that),
			'access_token' => $fanpage_token // note, we use the page token here
	 
	);
	 
	echo "<h3>POST Data</h3>";
	var_dump($args);
	
	
	echo "<h3>Facebook Api feedback</h3>"; 
	$photo = $facebook->api($album_id . '/photos', 'post', $args);
	var_dump($photo);
	
	
	echo "<h3>Results</h3>";
	if( is_array( $photo ) && ! empty( $photo['id'] ) )
	 
			echo 'Photo uploaded. Check it on Graph API Explorer. Photo ID: ' . $photo['id'];	


}


function getSslPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}



function get_signed_request()
{
    $signed_request = NULL;
	if (isset($_REQUEST["signed_request"]))
	{
		list($encoded_sig, $payload) = explode(".", $_REQUEST["signed_request"], 2);
		$sig                         = base64_decode(strtr($encoded_sig, "-_", "+/"));
		$signed_request              = json_decode(base64_decode(strtr($payload, "-_", "+/"), TRUE), TRUE);
	}
    return $signed_request;
}
?>