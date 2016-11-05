<?php

// Put your device token here (without spaces):
$deviceToken = 'Your Device Token';

// Put your private key's passphrase here:
$passphrase = 'certificate passphrase';

// Put your alert message here:
$message = 'My first push notification!';

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns_cert.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS Server' . PHP_EOL;

// Create the payload body
/*
   Check different payload options here https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/TheNotificationPayload.html
*/
$body['aps'] = array(
	'alert' => $message,
	'sound' => 'default',
	'category' => 'ACTIONABLE'
	);
$body['owb'] = array('msgid'=>21);

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered to APNS' . PHP_EOL;
else
	echo 'Message successfully delivered to APNS' . PHP_EOL;

// Close the connection to the server. This is important.
fclose($fp);
