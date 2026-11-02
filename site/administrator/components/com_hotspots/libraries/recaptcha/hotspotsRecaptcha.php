<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  Copyright 2011 Daniel Dimitrov. (http://compojoom.com)
 *  All rights reserved
 *
 *  This script is part of the Hotspots project. The Hotspots project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

defined('_JEXEC') or die('Restricted access');

/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class hotspotsRecaptcha {

	private $serverURLS = NULL;
	private $ReCaptchaResponse = NULL;

	public function __construct() {
		$this->serverURLS = new stdClass();
		$this->serverURLS->RECAPTCHA_API_SERVER = "https://www.google.com/recaptcha/api";
		$this->serverURLS->RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
		$this->serverURLS->RECAPTCHA_VERIFY_SERVER = "www.google.com";

		$this->ReCaptchaResponse = new stdClass();
		$this->ReCaptchaResponse->is_valid = NULL;
		$this->ReCaptchaResponse->error = NULL;

		$this->publicKey = HotspotsHelperSettings::get('recaptcha_public_key');
		$this->privateKey = HotspotsHelperSettings::get('recaptcha_private_key');

	}

	private function qsencode($data) {
		$req = "";
		foreach ($data as $key => $value) {
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}

		// Cut the last '&'
		$req = substr($req, 0, strlen($req) - 1);
		return $req;
	}

	private function httpPost($host, $path, $data, $port = 80) {

		$req = $this->qsencode($data);

		$http_request = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';
		if (false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) )) {
			die('Could not open socket');
		}

		fwrite($fs, $http_request);

		while (!feof($fs))
			$response .= fgets($fs, 1160); // One TCP-IP packet
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	public function getHtml($error = null, $use_ssl = false) {
		if ($this->publicKey == null || $this->publicKey == "") {
			die("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
		} else {
			
		}

		if ($use_ssl) {
			$server = $this->serverURLS->RECAPTCHA_API_SECURE_SERVER;
		} else {
			$server = $this->serverURLS->RECAPTCHA_API_SERVER;
		}

		$errorpart = "";
		if ($error) {
			$errorpart = "&amp;error=" . $error;
		} else {
			
		}
		return '<script type="text/javascript" src="' . $server . '/challenge?k=' . $this->publicKey . $errorpart . '"></script>
 
        <noscript>
            <iframe src="' . $server . '/noscript?k=' . $this->publicKey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
            <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
            <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
        </noscript>';
	}

	public function checkAnswer($challenge, $response, $extra_params = array()) {
		if ($this->privateKey == null || $this->privateKey == "") {
			die("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
		} else {
			
		}

		$remoteIp = $_SERVER['REMOTE_ADDR'];

		//discard spam submissions
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
			$this->ReCaptchaResponse->is_valid = false;
			$this->ReCaptchaResponse->error = "incorrect_captcha_sol";
			return $this->ReCaptchaResponse;
		}

		$response = $this->httpPost($this->serverURLS->RECAPTCHA_VERIFY_SERVER, '/recaptcha/api/verify', array(
					"privatekey" => $this->privateKey,
					"remoteip" => $remoteIp,
					"challenge" => $challenge,
					"response" => $response
						) + $extra_params
		);
		

		$answers = explode("\n", $response[1]);

		if (trim($answers [0]) == "true") {
			$this->ReCaptchaResponse->is_valid = true;
		} else {
			$this->ReCaptchaResponse->is_valid = false;
			$this->ReCaptchaResponse->error = str_replace("-", "_", $answers[1]);
		}
		return $this->ReCaptchaResponse;
	}

	private function getSignupUrl($domain = null, $appname = null) {
		return "http://recaptcha.net/api/getkey?" . $this->qsencode(array("domain" => $domain, "app" => $appname));
	}

	private function aesPad($val) {
		$block_size = 16;
		$numpad = $block_size - (strlen($val) % $block_size);
		return str_pad($val, strlen($val) + $numpad, chr($numpad));
	}

}
