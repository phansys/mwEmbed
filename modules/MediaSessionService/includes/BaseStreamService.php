<?php 
require_once( dirname( __FILE__ ) . '/../../KalturaSupport/KalturaCommon.php' );

class BaseStreamService {
	// stores the socket connection for realtime events
	var $clientSocket = null;
	
	function __construct(){
		global $container;
		$this->cache = $container['cache_helper'];
		$this->request = $container['request_helper'];
		$this->entryResult = $container['entry_result'];
		$this->uiConfResult = $container['uiconf_result'];
	}
	function setStreamUrl( $url ){
		$this->streamUrl = $url;
	}
	/* for now this is mostly for debugging */
	function sendSocketMessage( $message ){
		if( !$this->clientSocket ){
			include_once 'WebsocketClient.php';
			$this->clientSocket = new WebsocketClient();
			$this->clientSocket->connect('127.0.0.1', 8080, '/mwEmbedSocket', 'http://localhost');
		}
		$payload = json_encode( array(
			'action' => 'log',
			'message' => $message,
			'guid' => $this->getGuid()
		));
		$this->clientSocket->sendData( $payload );
	}
	function getStreamHandler(){
		// Grab and parse the base content m3u8
		$streamContent = $this->getStreamContent();
		// Replace all the URLs with kaltura service URLs ( only M3u8Handler supported right now )
		$streamHandler = new M3u8Handler( $streamContent );
		// add the standard set of service params:
		$streamHandler->setServiceParams (
			array(
				'uiconf_id' => $this->request->getUiConfId(),
				'wid' => $this->request->getWidgetId(),
				'entry_id' => $this->request->getEntryId(),
				'guid' => $this->getGuid()
			)
		);
		return $streamHandler;
	}
	function getStreamContent(){
		$content =  $this->cache->get( $this->getCacheKey() );
		if( $content ){
			return $content;
		} 
		$content = file_get_contents( $this->streamUrl );
		// TODO take into consideration headers or per stream cache metadata info. 
		$this->cache->set(  $this->getCacheKey(), $content );
		return $content;
	}
	function getGuid(){
		// gets the guid from the request, if not set in the request generates a guid to pass on to subsequent requests
		if( $this->request->get( 'guid' ) ){
			return  $this->request->get( 'guid' ) ;
		}
		// TODO: check for cookie or session based guid
		// for now just, generate with php: 
		return uniqid();
	}
	function getCacheKey(){
		return md5( $this->streamUrl );
	}
}