<?php

class myCurl {
	protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';
	protected $_url;
	protected $_followlocation;
	protected $_timeout;
	protected $_maxRedirects;
	protected $_cookieFileLocation = './cookie.txt';
	protected $_post;
	protected $_postFields;
	protected $_referer = "http://www.google.com";
	protected $_Headers = array();

	protected $_session;
	protected $_webpage;
	protected $_includeHeader;
	protected $_noBody;
	protected $_status;
	protected $_error;
	protected $_binaryTransfer;
	protected $_responseHeader;
	public $authentication = 0;
	public $auth_name = '';
	public $auth_pass = '';

	public function useAuth($use) {
		$this->authentication = ($use == true) ? 1 : 0;
	}

	public function setName($name) {
		$this->auth_name = $name;
	}
	
	public function setPass($pass) {
		$this->auth_pass = $pass;
	}

	public function __construct($url, $followlocation = true, $timeOut = 30, $maxRedirecs = 4, $binaryTransfer = false, $includeHeader = false, $noBody = false) {
		$this->_url = $url;
		$this->_followlocation = $followlocation;
		$this->_timeout = $timeOut;
		$this->_maxRedirects = $maxRedirecs;
		$this->_noBody = $noBody;
		$this->_includeHeader = $includeHeader;
		$this->_binaryTransfer = $binaryTransfer;
		$this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';
	}

	public function setReferer($referer) {
		$this->_referer = $referer;
	}

	public function setCookiFileLocation($path) {
		$this->_cookieFileLocation = $path;
	}

	public function setPost($postFields) {
		$this->_post = true;
		$this->_postFields = $postFields;
	}

	public function setUserAgent($userAgent) {
		$this->_useragent = $userAgent;
	}
	
	public function setHeaders($Headers) {
		$this->_Headers = $Headers;
	}
	
	public function setBinary($binary) {
		$this->_binaryTransfer = $binary;
	}

	public function createCurl($url = 'nul', $headers = true) {
		if($url != 'nul')
			$this->_url = $url;

		$s = curl_init();
		
		curl_setopt($s, CURLOPT_URL, $this->_url);
		//curl_setopt($s,CURLOPT_HTTPHEADER,array('Expect:'));
		curl_setopt($s, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($s, CURLOPT_MAXREDIRS, $this->_maxRedirects);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($s, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
		curl_setopt($s, CURLOPT_COOKIEJAR, $this->_cookieFileLocation);
		curl_setopt($s, CURLOPT_COOKIEFILE, $this->_cookieFileLocation);
		curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($s, CURLOPT_AUTOREFERER, true);
		curl_setopt($s, CURLOPT_MAXREDIRS, 10);

		//curl_setopt($s, CURLOPT_FAILONERROR, true);

		if($this->authentication == 1)
			curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
		
		if($this->_post) {
			curl_setopt($s, CURLOPT_POST,true);
			curl_setopt($s, CURLOPT_POSTFIELDS, $this->_postFields);
		}

		if($this->_includeHeader)
			curl_setopt($s, CURLOPT_HEADER,true);

		if($this->_noBody)
			curl_setopt($s, CURLOPT_NOBODY,true);
		
		/*if($this->_binary)
			curl_setopt($s,CURLOPT_BINARYTRANSFER,true);
		*/
		
		if(count($this->_Headers) > 0)
			curl_setopt($s, CURLOPT_HTTPHEADER, $this->_Headers);
		
		curl_setopt($s, CURLOPT_USERAGENT, $this->_useragent);
		curl_setopt($s, CURLOPT_REFERER, $this->_referer);

		$this->_webpage = curl_exec($s);
		
		if($this->_webpage === false)
			$this->_error = curl_error($s);
		
		$this->_status = curl_getinfo($s, CURLINFO_HTTP_CODE);
		$response = curl_getinfo($s);
		if($headers)
			$this->_responseHeader = get_headers($response['url']);
	}
	
	public function doGet($url, $headers = false) {
		$this->_post = false;
		if($this->_referer == null)
			$this->setReferer = $this->_url;
		$this->createCurl($url, $headers);
	}
	
	public function doPost($url, $fields, $toString = true, $headers = false) {
		if($toString)
			foreach($fields as $k => $v)
				$post_string[] = $k.'='.$v;
		$this->_post = true;
		$this->_postFields = ($toString) ? implode('&', $post_string) : $fields;
		if($this->_referer == null)
			$this->setReferer = $this->_url;
		$this->createCurl($url, $headers);
	}

	public function getHttpStatus() {
		return $this->_status;
	}
	
	public function getResponseHeader() {
		return $this->_responseHeader;
	}
	
	public function getError() {
		return $this->_error;
	}

	public function __tostring() {
		return $this->_webpage;
	}
	
	public function getContents() {
		return $this->_webpage;
	}
	
	public function clearCookies() {
		@unlink($this->_cookieFileLocation);
	}
	
	public function setFollowLocation($fl) {
		$this->_followlocation = $fl;
	}

}

?>
