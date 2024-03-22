<?php
class SynoDLMSearchRuTracker {
	private $proxy = 'http://127.0.0.1:54321/?q=';
	private $url = 'https://rutracker.org/forum/';
	private $login_uri = 'login.php';
	private $query_uri = 'tracker.php?nm=%s';
	private $torrent_uri = 'dl.php?t=%s';
	private $cookie = '/tmp/rutracker.cookie';

	public function prepare($curl, $query, $login, $password) {
		// Login
		$opts = [
			'url' => $this->url . $this->login_uri,
			'method' => 'POST',
			'postfields' => 'login_username=' . urlencode($login) . '&login_password='. urlencode($password) . '&login=%C2%F5%EE%E4',
			'cookiejar' => $this->cookie,
			'proxy' => true
		];

		$ch = curl_init($this->proxy . $this->encode($opts));
		curl_exec($ch);
		curl_close($ch);
		

		// Query torrent list
		$opts = [
			'url' => sprintf($this->url . $this->query_uri, urlencode($query)),
			'cookiefile' => $this->cookie,
			'proxy' => true
		];
		curl_setopt($curl, CURLOPT_URL, $this->proxy . $this->encode($opts));

		return true;
	}
	
	public function parse($plugin, $response) {
		$input = iconv('cp1251', 'UTF-8', $response);
		
		$regexp2 = "<tr.*class=\"tCenter hl-tr\" data-topic_id=\"[0-9]+\">(.*)<\/tr>";
		$regexp_category = "<a.*tracker.php\?f=([0-9]+)\">(.*)<\/a>";
		$regexp_title = "<a.*(viewtopic.php\?t=[0-9]+)\">(.*)<\/a>";
		$regexp_download = "dl.php?\?t=([0-9]+)\"";
		$regexp_size = "tor-size\" data-ts_text=\"([0-9]+)\"";
		$regexp_datetime = "2px;\".*data-ts_text=\"([0-9]+)\"";
		$regexp_seeds = "<b class=\"seedmed\">([0-9]+)<";
		$regexp_leechs = "<td.*\"row4 leechmed.*>([0-9]+)<";
		
		$res = 0;
		if(preg_match_all("/$regexp2/siU", $input, $matches2, PREG_SET_ORDER)) {
			foreach($matches2 as $match2) {
				$title = 'Unknown title';
				$download = 'Unknown download';
				$size = 0;
				$datetime = '1970-01-01';
				$page = 'Default page';
				$hash = 'Hash unknown';
				$seeds = 0; 
				$leechs = 0;
				$category = 'Unknown category';
	
				if(preg_match_all("/$regexp_category/siU", $match2[0], $matches, PREG_SET_ORDER)) {
						foreach($matches as $match) {
							$category = $match[2];
						}
				}
	
				if(preg_match_all("/$regexp_title/siU", $match2[0], $matches, PREG_SET_ORDER)) {
						foreach($matches as $match) {
							$page = $this->url.$match[1];
							$title = str_replace('<wbr>', '', $match[2]);
							$hash = md5($res.$title);
						}
				}
	
				if(preg_match_all("/$regexp_download/siU", $match2[0], $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$opts = [
							'url' => sprintf($this->url . $this->torrent_uri, $match[1]),
							'filename' => 'Loading...',
							'mime' => 'application/x-bittorrent',
							'cookiefile' => $this->cookie,
							'useragent' => DOWNLOAD_STATION_USER_AGENT,
							'proxy' => true
						];

						$download = $this->proxy . $this->encode($opts);
					}
				}

				if(preg_match_all("/$regexp_size/siU", $match2[0], $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$size = $match[1];
					}
				}

				if(preg_match_all("/$regexp_datetime/siU", $match2[0], $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$datetime = date('Y-m-d H:i',$match[1]);
					}
				}

				if(preg_match_all("/$regexp_seeds/siU", $match2[0], $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$seeds = $match[1];
					}
				}

				if(preg_match_all("/$regexp_leechs/siU", $match2[0], $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$leechs = $match[1];
					}
				}

				if ($title != 'Unknown title') {
					$plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
					$res++;
				}
			}
		}

		return $res;
	}

	protected function encode($opts) {
		return urlencode(base64_encode(serialize($opts)));
	}
}
?>