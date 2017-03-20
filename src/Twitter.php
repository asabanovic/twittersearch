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
	protected $client;

	/**
	 * Set Twitter API base URI
	 */
	public $api_base = 'https://api.twitter.com/';

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
	 * @param string $app_key	Twitter Dev App Key
	 * @param string $app_secret	Twitter Dev App Secret 
	 * @return void
	 */
	public function __construct($app_key = null, $app_secret = null)
	{
		
		$this->client = new \GuzzleHttp\Client();	

		$this->app_key = $app_key;

		$this->app_secret = $app_secret;

		if ($this->app_key == null || $this->app_secret == null) {
			return false;
		}

		$this->getBearerToken();

	}

	/**
	 * Pull tweets from API
	 *
	 * @param string $query 	'q' type of query
	 * @param integer $count 	Define a number of search results
	 * @return void
	 */
	public function searchTweets($query, $count = 10)
	{	
		if ($this->bearer_token == null) {
			return false;
		}

		try { 

			$response = $this->client->request('GET', $this->api_base.'1.1/search/tweets.json', [
			    'query' => ['q' => $query, 'count' => $count],
			    'headers' => [
			        'Authorization' => 'Bearer ' . $this->bearer_token
			    ]
			]);

			$this->setStatusCode($response->getStatusCode());

			$response = json_decode($response->getBody());

			$this->response = $response->statuses;

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

			$response = $this->client->request('POST', $this->api_base.'oauth2/token', [
			    'query' => ['grant_type' => 'client_credentials'],
			    'headers' => [
			        'Authorization' => 'Basic ' . base64_encode($this->app_key.':'.$this->app_secret),
			        'Content-Type'     => 'application/x-www-form-urlencoded;charset=UTF-8'
			    ]
			]);

			$this->setStatusCode($response->getStatusCode());

			$response = json_decode($response->getBody());

			$this->bearer_token = $this->isBearer($response);

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