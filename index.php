<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


include_once "src/facebook.php";

function get_signed_request()
{
    $signed_request = NULL;

	if (isset($_REQUEST["signed_request"]))
	{
		list($encoded_sig, $payload) = explode(".", $_REQUEST["signed_request"], 2);
		$sig                         = base64_decode(strtr($encoded_sig, "-_", "+/"));
		$signed_request              = json_decode(base64_decode(strtr($payload, "-_", "+/"), TRUE), TRUE);
	}
	else
	{
		error();
	}
	
    return $signed_request;
}


$facebook = new Facebook(array(
  'appId'  => '253204234752974',
  'secret' => '9b12db97de75600be77e679e5d7ef7a6',
  'fileUpload' => true
));
 
echo "<h3>Facebook</h3>";
print_r($facebook);
echo "<br>";

$user = $facebook->getUser();
$access_token = $facebook->getAccessToken();

echo "<h3>Access token</h3><a href='https://developers.facebook.com/tools/access_token/' target='_blank'>https://developers.facebook.com/tools/access_token/</a><br>";
var_dump($access_token);

$my_url = "https://royalepromotions.ca/facebook/imageupload/index.php";

if (isset($_REQUEST["code"]))
{
    header("Location: http://www.facebook.com/mmdevel/app_253204234752974");
    exit;
}

$signed_request = $facebook->getSignedRequest();
if (!isset($signed_request["user_id"]))
    {
	$params = array(
	  'scope' => 'publish_stream',
	  'redirect_uri' => $my_url
	);

	echo "<script type='text/javascript'>top.location.href = '" . $facebook->getLoginUrl($params) . "';</script>";
	exit;
}

	
//$signed_request = get_signed_request();
echo "<h3>Signed Request</h3>";
var_dump($signed_request);


echo "<h3>user</h3>";
print_r($user);

if (!empty($user)) {
	
	//https://developers.facebook.com/tools/access_token/
	//$access_token = 'AAADmSbR3084BAMTu9ZC3y8ZBQZAcVpkbLiNTOyaZAaSq84xsMmqJSSsOZAe5ouGhV7iyiBaFByJoiGvsudX590htp9ZCnnbLoCRpZBk8643HNWjk1N01g1i';

	$params = array(
		'access_token' => $access_token
	);
	 
	//https://www.facebook.com/pages/edit/?id=183866985000238&sk=basic
	$fanpage = '237445739630557';
	 
	 
	
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
			
			if( $account['id'] == $fanpage || $account['name'] == $fanpage )
	 
					$fanpage_token = $account['access_token'];
	 
	}
	echo "</ul>";
	$fanpage_token = 'AAAGMjThQ5NYBANm5cLVfoDswfm9L3Nh47IrI9tRQjYwMZB8ZCvOET57U5rcjazn2LElb4f4RUOqlg0jPpi9JZAhZCLqz3yZBZAPcUXL3WUsix1JbShA6aW2CnrXGMyHv4ZD';
	 
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
	
	
	$album_id = '472992669421000'; // Get the first one. Shouldn't be empty!
	
	
	echo "<h3>album_id  </h3><a href='http://www.facebook.com/media/set/?set=a.472992669421000.1073741825.183866985000238&type=3'>http://www.facebook.com/media/set/?set=a.472992669421000.1073741825.183866985000238&type=3</a><br>";
	var_dump($album_id);
	 
	 
	 
	// Upload the photo (previously uploaded by user)
	 
	$args = array(
	 
			'message' => 'Von das scheirber.',
			'image' => '@MikeBastarache-Thumd.jpg',
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
?>