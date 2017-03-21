<?php

namespace TwitterSearch;

/**
 * Check documentation https://dev.twitter.com/oauth/application-only
 */
class Twitter
{
	/**
	 * Define GuzzleHttp Client
	 */
	public $client;

	/**
	 * Set Twitter API base URI
	 */
	public static $api_base = 'https://api.twitter.com/';

	/**
	 * App key defined under your dev twitter account
	 */
	protected $app_key;

	/**
	 * App secret defined under your dev twitter account
	 */
	protected $app_secret;

	/**
	 * Bearer token retrieved after the initial authorization
	 */
	protected $bearer_token;

	/**
	 * Status code of a request
	 */
	protected $status_code;

	/**
	 * Twitter API response
	 */
	protected $response;

	/**
	 * Create HTTP client and obtain bearer token
	 *
	 * @param object $client	Your HTTP client (GuzzleHttp require-dev)
	 * @param string $app_key	Twitter Dev App Key
	 * @param string $app_secret	Twitter Dev App Secret 
	 * @return void
	 *
	 * @throws Exception
	 */
	public function __construct($client, $app_key = null, $app_secret = null)
	{
		$this->client = $client;	
		$this->app_key = $app_key;
		$this->app_secret = $app_secret;

		if ($this->app_key == null || $this->app_secret == null) {
			throw new \Exception('Twitter requires app_key and app_secret in order to make calls to the API');
		}

		$this->getBearerToken();

	}

	/**
	 * Pull tweets from API
	 *
	 * @param string $query 	'q' type of query
	 * @param integer $count 	Define a number of search results
	 * @return void
	 *
	 * @throws Exception
	 */
	public function searchTweets($query, $count = 10)
	{	
		if ($this->bearer_token == null) {
			throw new \Exception('Incorrect token. You need to obtain correct Bearer token in order to make calls to the API. Check if your API keys are correct.');
		}

		$this->makeRequest($query, $count);
	}

	/**
	 * Get raw Twitter response
	 * @param string $query 	'q' type of query
	 * @param integer $count 	Define a number of search results
	 * @return object 	
	 */
	public function makeRequest($query, $count)
	{

		try { 
 			
 			$response = $this->client->request('GET', self::$api_base.'1.1/search/tweets.json', [
			    'query' => ['q' => $query, 'count' => $count],
			    'headers' => [
			        'Authorization' => 'Bearer ' . $this->bearer_token
			    ]
			]);
  			
			$this->setStatusCode($response->getStatusCode());

			$response_body = json_decode($response->getBody());
 			$this->response = $response_body->statuses;
 
 		} catch (\Exception $e) {

			$this->setStatusCode($e->getCode());
			$this->setResponse($e->getMessage());

		}
	}

	/**
	 * Make basic (initial) authorization to obtain bearer token to be
	 * used in future search requests
	 *
	 * @return void
	 */
	public function getBearerToken()
	{	
		try { 

			$response = $this->client->request('POST', self::$api_base.'oauth2/token', [
			    'query' => ['grant_type' => 'client_credentials'],
			    'headers' => [
			        'Authorization' => 'Basic ' . base64_encode($this->app_key.':'.$this->app_secret),
			        'Content-Type'     => 'application/x-www-form-urlencoded;charset=UTF-8'
			    ]
			]);

 			$this->setStatusCode($response->getStatusCode());
 			$response_body = json_decode($response->getBody());
 			$this->bearer_token = $this->isBearer($response_body);

 		} catch (\Exception $e) {

			$this->setStatusCode($e->getCode());
			$this->setResponse($e->getMessage());

		}
		
 	}

 	/**
 	 * Validate the initial response to check if the response has 
 	 * a bearer token
 	 *
 	 * @param object $response	Stores access token
 	 * @return void
 	 */
	public function isBearer($response)
	{	
     	if (!isset($response->token_type)) {
    		return false;
    	}

    	if ($response->token_type == 'bearer') {
    		return $response->access_token;
    	}
	}

	/**
	 * Get Twitter response status code
	 *
	 * @return integer Status code
	 */
	public function getStatusCode()
	{
		return $this->status_code;
	}

	/**
	 * Set Twitter response status code
	 */
	public function setStatusCode($code)
	{
		$this->status_code = $code;
	}

	/**
	 * Get Twitter response
	 *
	 * @return mixed 	Error string or array of tweets
	 */
	public function getResponse()
	{	
		return $this->response;
	}

	/**
	 * Set Twitter response
	 */
	public function setResponse($response)
	{	
		$this->response = $response;
	}

}