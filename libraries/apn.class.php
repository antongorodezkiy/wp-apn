<?php
# -*- coding: utf-8 -*-
##
##     Copyright (c) 2010 Benjamin Ortuzar Seconde <bortuzar@gmail.com>
##
##     This file is part of APNS.
##
##     APNS is free software: you can redistribute it and/or modify
##     it under the terms of the GNU Lesser General Public License as
##     published by the Free Software Foundation, either version 3 of
##     the License, or (at your option) any later version.
##
##     APNS is distributed in the hope that it will be useful,
##     but WITHOUT ANY WARRANTY; without even the implied warranty of
##     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
##     GNU General Public License for more details.
##
##     You should have received a copy of the GNU General Public License
##     along with APNS.  If not, see <http://www.gnu.org/licenses/>.
##
##
## $Id: Apns.php 168 2010-08-28 01:24:04Z Benjamin Ortuzar Seconde $
##
#######################################################################
/*
 * Modified and adapted for Wordpress
 * (c) Anton_Gorodezkiy, antongorodezkiy@gmail.com
 * 2014
*/

/**
 * Apple Push Notification Server
 */
class WPAPN_APN
{

/*******************************
	PROTECTED : */

	
	
	protected $server;
	protected $keyCertFilePath;
	protected $passphrase;
	protected $pushStream;
	protected $feedbackStream;
	protected $timeout;
	protected $idCounter = 0;
	protected $expiry;
	protected $debug = true;
	protected $allowReconnect = true;
	protected $additionalData = array();
	protected $apnResonses = array(
		0 => 'No errors encountered',
		1 => 'Processing error',
		2 => 'Missing device token',
		3 => 'Missing topic',
		4 => 'Missing payload',
		5 => 'Invalid token size',
		6 => 'Invalid topic size',
		7 => 'Invalid payload size',
		8 => 'Invalid token',
		255 => 'None (unknown)',
	);
	
	private $connection_start;
	
	public $error;
	public $payloadMethod = 'simple';
	public $recent_payload = null;
	
	/**
	* Connects to the server with the certificate and passphrase
	*
	* @return <void>
	*/
	protected function connect($server) {

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->keyCertFilePath);
		
		if ($this->passphrase) {
			stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
		}

		
		$stream = stream_socket_client($server, $err, $errstr, $this->timeout, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		
		$this->log('debug',"APN: Maybe some errors: $err: $errstr");
		
		
		if (!$stream) {
			
			if ($err) {
				$this->log('error',"APN Failed to connect: $err $errstr");
				//throw new Exception("APN Failed to connect: $err $errstr");
			}
			else {
				$this->log('error',"APN Failed to connect: Something wrong with context");
				//throw new Exception("APN Failed to connect: Something wrong with context");
			}
				
			return false;
		}
		else {
			stream_set_timeout($stream,20);
			$this->log('debug',"APN: Opening connection to: {$server}");
			return $stream;
		}
	}
	
	
	
	/**
	* Generates the payload
	* 
	* @param <string> $message
	* @param <int> $badge
	* @param <string> $sound
	* @return <string>
	*/
	protected function generatePayload($message, $badge = NULL, $sound = NULL, $newstand = false) {

	   $body = array();

	   // additional data
			if (is_array($this->additionalData) && count($this->additionalData))
			{
				$body = $this->additionalData;
			}

		//message
			$body['aps'] = array('alert' => $message);

		//badge
			if ($badge)
				$body['aps']['badge'] = $badge;
			
			if ($badge == 'clear')
				$body['aps']['badge'] = 0;

		 //sound
			if ($sound)
				$body['aps']['sound'] = $sound;

		//newstand content-available
			if($newstand)
				$body['aps']['content-available'] = 1;
				

	   $payload = json_encode($body);
	   $this->log('debug',"APN: generatePayload '$payload'");
	   return $payload;
	}
	
	
	
	/**
	 * Writes the contents of payload to the file stream
	 * 
	 * @param <string> $deviceToken
	 * @param <string> $payload
	 */
	protected function sendPayloadSimple($deviceToken, $payload){

		$this->idCounter++;		

		$this->log('debug',"APN: sendPayloadSimple to '$deviceToken'");

		$msg = chr(0) 									// command
			. pack('n',32)									// token length
			. pack('H*', $deviceToken)						// device token
			. pack('n',strlen($payload))					// payload length
			. $payload;										// payload
		
		$this->log('debug',"APN: payload: '$msg'");
		$this->log('debug',"APN: payload length: '".strlen($msg)."'");
		$result = fwrite($this->pushStream, $msg, strlen($msg));
		
		if ($result)
			return true;
		else
			return false;
	}
	
	
	/**
	 * Writes the contents of payload to the file stream with enhanced api (expiry, debug)
	 * 
	 * @param <string> $deviceToken
	 * @param <string> $payload
	 */
	protected function sendPayloadEnhance($deviceToken, $payload, $expiry = 86400) {
		
		$this->recent_payload = $payload;
		
		if (!is_resource($this->pushStream))
			$this->reconnectPush();
		
		$this->idCounter++;		

		$this->log('debug',"APN: sendPayloadEnhance to '$deviceToken'");
		
		$payload_length = strlen($payload);

		$request = chr(1) 										// command
						. pack("N", time())		 				// identifier
						. pack("N", time() + $expiry) // expiry
						. pack('n', 32)								// token length
						. pack('H*', $deviceToken) 		// device token
						. pack('n', $payload_length) 	// payload length
						. $payload;

		$request_unpacked = @unpack('Ccommand/Nidentifier/Nexpiry/ntoken_length/H64device_token/npayload_length/A*payload', $request); // payload

		$this->log('debug', "APN: request: '$request'");
		$this->log('debug', "APN: unpacked request: '" . print_r($request_unpacked, true) . "'");
		$this->log('debug', "APN: payload length: '" . $payload_length . "'");
		$result = fwrite($this->pushStream, $request, strlen($request));
		
		if ($result)
		{
			return $this->getPayloadStatuses();
		}
	
		return false;
	}
	
	
	protected function timeoutSoon($left_seconds = 5)
	{
		$t = ( (round(microtime(true) - $this->connection_start) >= ($this->timeout - $left_seconds)));
		return (bool)$t;
	}
	
	
	
/* 	PROTECTED ^ 
*******************************/

        
	/**
	 * Connects to the APNS server with a certificate and a passphrase
	 */
	public function __construct($settings) {
		
		$is_sandbox = $settings['Sandbox'];
		
		if ($is_sandbox) {
			$PermissionFile = ABSPATH.$settings['PermissionFileSandbox'];
			$PassPhrase = $settings['PassPhraseSandbox'];
			$PushGateway = $settings['PushGatewaySandbox'];
			$FeedbackGateway = $settings['FeedbackGatewaySandbox'];
		}
		else {
			$PermissionFile = ABSPATH.$settings['PermissionFile'];
			$PassPhrase = $settings['PassPhrase'];
			$PushGateway = $settings['PushGateway'];
			$FeedbackGateway = $settings['FeedbackGateway'];
		}
		
		if(!file_exists($PermissionFile) || !is_file($PermissionFile)) {
			throw new Exception("APN Failed to connect: APN Permission file not found");
		}
		
		$this->debug = $settings['debug'];
		$this->pushServer = $PushGateway;
		$this->feedbackServer = $FeedbackGateway;
		$this->keyCertFilePath = $PermissionFile;
		$this->passphrase = $PassPhrase;
		$this->timeout = $settings['Timeout'] ? $settings['Timeout'] : 60;
		$this->expiry = $settings['Expiry'] ? $settings['Expiry'] : 86400;
	}

	
	public function log($type, $message) {
		if ($type == 'error' || $this->debug) {
			WPAPN_Plugin::log($type,$message);
		}
	}
	
	public function setDebug($debug_enabled) {
		$this->debug = $debug_enabled;
	}
    
	
	/**
	 * Public connector to push service
	 */
	public function connectToPush()
	{
		if (!$this->pushStream or !is_resource($this->pushStream))
		{
			$this->log('debug',"APN: connectToPush");
		
			$this->pushStream = $this->connect($this->pushServer);
			
			if ($this->pushStream)
			{
				$this->connection_start = microtime(true);
				//stream_set_blocking($this->pushStream,0);
			}
		}
		
		return $this->pushStream;
	}
	
	/**
	 * Public connector to feedback service
	 */
	public function connectToFeedback()
	{
		$this->log('info',"APN: connectToFeedback");
		return $this->feedbackStream = $this->connect($this->feedbackServer);
	}
	
	/**
	 * Public diconnector to push service
	 */
	public function disconnectPush()
	{
		$this->log('debug',"APN: disconnectPush");
		if ($this->pushStream && is_resource($this->pushStream))
		{
			$this->connection_start = 0;
			return @fclose($this->pushStream);
		}
		else
			return true;
	}
	
	/**
	 * Public disconnector to feedback service
	 */
	public function disconnectFeedback()
	{
		$this->log('info',"APN: disconnectFeedback");
		if ($this->feedbackStream && is_resource($this->feedbackStream))
			return @fclose($this->feedbackStream);
		else
			return true;
	}
	
	public function reconnectPush()
	{
		$this->disconnectPush();
				
		if ($this->connectToPush())
		{
			$this->log('debug',"APN: reconnect");
			return true;
		}
		else
		{
			$this->log('debug',"APN: cannot reconnect");
			return false;
		}
	}
	
	public function tryReconnectPush()
	{
		if ($this->allowReconnect)
		{
			if($this->timeoutSoon())
			{
				return $this->reconnectPush();
			}
		}
		
		return false;
	}
	
        
	/**
	 * Sends a message to device
	 * 
	 * @param <string> $deviceToken
	 * @param <string> $message
	 * @param <int> $badge
	 * @param <string> $sound
	 */
	public function sendMessage($deviceToken, $message, $badge = NULL, $sound = NULL, $expiry = '', $newstand = false)
	{
		$this->error = '';
		
		if (!ctype_xdigit($deviceToken))
		{
			$this->log('debug',"APN: Error - '$deviceToken' token is invalid. Provided device token contains not hexadecimal chars");
			$this->error = 'Invalid device token. Provided device token contains not hexadecimal chars';
			return false;
		}
		
		// restart the connection
		$this->tryReconnectPush();
		
		$this->log('info',"APN: sendMessage '$message' to $deviceToken");
		
		//generate the payload
		$payload = $this->generatePayload($message, $badge, $sound, $newstand);

		$deviceToken = str_replace(' ', '', $deviceToken);
		
		//send payload to the device.
		if ($this->payloadMethod == 'simple')
			$this->sendPayloadSimple($deviceToken, $payload);
		else
		{
			if (!$expiry)
				$expiry = $this->expiry;
			
			return $this->sendPayloadEnhance($deviceToken, $payload, $expiry);
		}
	}


	/**
	 * Writes the contents of payload to the file stream
	 * 
	 * @param <string> $deviceToken
	 * @param <string> $payload
	 * @return <bool> 
	 */
	public function getPayloadStatuses()
	{
		
		$read = array($this->pushStream);
		$null = null;
		$changedStreams = stream_select($read, $null, $null, 0, 2000000);

		if ($changedStreams === false)
		{    
			$this->log('error',"APN Error: Unabled to wait for a stream availability");
		}
		elseif ($changedStreams > 0)
		{
			
			$responseBinary = fread($this->pushStream, 6);
			if ($responseBinary !== false || strlen($responseBinary) == 6) {
				
				if (!$responseBinary)
					return true;
				
				$response = @unpack('Ccommand/Cstatus_code/Nidentifier', $responseBinary);
				
				$this->log('debug','APN: debugPayload response - '.print_r($response,true));
				
				if ($response && $response['status_code'] > 0)
				{
					$this->log('error','APN: debugPayload response - status_code:'.$response['status_code'].' => '.$this->apnResonses[$response['status_code']]);
					$this->error = $this->apnResonses[$response['status_code']];
					return false;
				}
				else
				{
					if (isset($response['status_code']))
						$this->log('debug','APN: debugPayload response - '.print_r($response['status_code'],true));
				}
				
			}
			else
			{
				$this->log('debug',"APN: responseBinary = $responseBinary");
				return false;
			}
		}
		else
			$this->log('debug',"APN: No streams to change, $changedStreams");
		
		return true;
	}



	/**
	* Gets an array of feedback tokens
	*
	* @return <array>
	*/
	public function getFeedbackTokens() {
	    
		$this->log('debug',"APN: getFeedbackTokens {$this->feedbackStream}");
		$this->connectToFeedback();
		
	    $feedback_tokens = array();
	    //and read the data on the connection:
	    while(!feof($this->feedbackStream)) {
	        $data = fread($this->feedbackStream, 38);
	        if(strlen($data)) {	   
	        	//echo $data;     	
	            $feedback_tokens[] = unpack("N1timestamp/n1length/H*devtoken", $data);
	        }
	    }
		
		$this->disconnectFeedback();
		
	    return $feedback_tokens;
	}

	
	/**
	* Sets additional data which will be send with main apn message
	*
	* @param <array> $data
	* @return <array>
	*/
	public function setData($data)
	{
		if (!is_array($data))
		{
			$this->log('error',"APN: cannot add additional data - not an array");
			return false;
		}
		
		if (isset($data['apn']))
		{
			$this->log('error',"APN: cannot add additional data - key 'apn' is reserved");
			return false;
		}
		
		return $this->additionalData = $data;
	}
	


	/**
	* Closes the stream
	*/
	public function __destruct(){
		$this->disconnectPush();
		$this->disconnectFeedback();
	}

}//end of class


