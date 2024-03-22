<?php
	$GET = unserialize(base64_decode($_GET['q'] ?? ''));
	
	$url = $GET['url'] ?? '';
	$useragent = $GET['useragent'] ?? 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0';
	$method = $GET['method'] ?? 'GET';
	$postfields = $GET['postfields'] ?? false;
	$encoding = $GET['encoding'] ?? '';
	$filename = $GET['filename'] ?? false;
	$mime = $GET['mime'] ?? 'application/octet-stream';

	$header = $GET['header'] ?? false;
	$cookie = $GET['cookie'] ?? false;
	$cookiejar = $GET['cookiejar'] ?? false;
	$cookiefile = $GET['cookiefile'] ?? false;

	$proxy = isset($GET['proxy']);
	$proxy_ip = getenv('PROXY_IP') ?? false;	
	$proxy_port = intval(getenv('PROXY_PORT') ?? 0);
	$proxy_type = [
		'http' => CURLPROXY_HTTP,	
		'https' => CURLPROXY_HTTPS,
		'socks4' => CURLPROXY_SOCKS4,
		'socks4a' => CURLPROXY_SOCKS4A,
		'socks5' => CURLPROXY_SOCKS5,
		'socks5_hostname' => CURLPROXY_SOCKS5_HOSTNAME
		][strtolower(getenv('PROXY_TYPE') ?? 'socks5')] ?? CURLPROXY_SOCKS5;
	$attemptCount = intval(getenv('PROXY_ATTEMPTS') ?? 3);

	$options = [
		CURLOPT_URL => $url,
		CURLOPT_USERAGENT => $useragent,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_ENCODING => $encoding,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60
	];

	if ($proxy && $proxy_ip && $proxy_port) {
		$options[CURLOPT_PROXYTYPE] = $proxy_type;
		$options[CURLOPT_PROXY] = "{$proxy_ip}:{$proxy_port}";
	}

	if ($header)
		$options[CURLOPT_HTTPHEADER] = $header;

	if ($cookie)
		$options[CURLOPT_HTTPHEADER] = array("Cookie: {$cookie}");

	if ($cookiejar)
		$options[CURLOPT_COOKIEJAR] = $cookiejar;

	if ($cookiefile)
		$options[CURLOPT_COOKIEFILE] = $cookiefile;

	if ($postfields)
		$options[CURLOPT_POSTFIELDS] = $postfields;	

	$res = false;
	$error = false;
	$attemptNo = 0;
	while ($attemptNo < $attemptCount && !$res) {
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$res = curl_exec($ch);
		if (!$res) {	
			$error = curl_error($ch);
			sleep(3);
		}
		curl_close($ch);
		
		$attemptNo++;		
	}

	if ($res) {
		if ($filename) {
			header("Content-Type: {$mime}");
			header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . strlen($res));
			header('Expires: 0');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Pragma: public');
			header('Pragma: no-cache');
		} 

		if ($_SERVER['REQUEST_METHOD'] != 'HEAD')
			echo $res;		
	} else {
		echo $error ?? '';
	}
?>