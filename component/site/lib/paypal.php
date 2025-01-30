<?php
use League\OAuth2\Client\Token\AccessToken;

class PayPalService {

    private $provider;
    private $accessToken;
    private $expires;
    private $integration;
    public $error;
    public $errorRequest;
    public $apiKey;
    private $apiSecret;
    private $apiMode;

    private $url = '';

    public function __construct($key,$secret,$mode)
    {
        $this->apiKey = $key;
        $this->apiSecret = $secret;
        $this->apiMode = $mode;
        $this->getClient();
    }

    protected function getProvider() {
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $this->apiKey,    // The client ID assigned to you by the provider
            'clientSecret'            => $this->apiSecret,   // The client password assigned to you by the provider
            'redirectUri'             => 'refreshonly',
            'urlAuthorize'            => $this->url . '/v1/oauth2/token',
            'urlAccessToken'          => $this->url . '/v1/oauth2/token',
            'urlResourceOwnerDetails' => 'none'
        ]);

        return $provider;
    }

    // get infusiosoft client, refresh access token if needed
    public function getClient() {

            // set url based on mode
        if ($this->apiMode == 'sandbox') $this->url = 'https://api-m.sandbox.paypal.com';
        else $this->url = 'https://api-m.paypal.com';

        $this->provider = $this->getProvider();

        // Get access token
        $options = [];
        $options['headers']['Authorization'] = 'Basic '.base64_encode($this->apiKey.':'.$this->apiSecret);
        $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $options['body'] = 'grant_type=client_credentials';

        $request = $this->provider->getRequest('POST', $this->url . '/v1/oauth2/token', $options);

        try {
            $response = $this->provider->getResponse($request);
        } catch ( BadResponseException $e ) {
            $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['body'=>[],'url'=>'/v1/oauth2/token','method'=>'POST'];
            return false;
        }

        $newAccessToken = json_decode($response->getBody(),true);

        $this->accessToken = $newAccessToken['access_token'];
        $this->expires = time() + $newAccessToken['expires_in'];


        $this->accessToken = new AccessToken([
            'access_token' => $this->accessToken,
            'expires' => $this->expires,
            'vales' => ['type'=>'bearer']
        ]);

        // Rferesh accaess token if expired
        if ( $this->accessToken->hasExpired()) {
            $options = [];
            $options['headers']['Authorization'] = 'Basic '.base64_encode($this->apiKey.':'.$this->apiSecret);
            $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            $options['body'] = 'grant_type=client_credentials';

            $request = $this->provider->getRequest('POST', $this->url . '/v1/oauth2/token', $options);

            try {
                $response = $this->provider->getResponse($request);
            } catch ( BadResponseException $e ) {
                $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
                $this->errorRequest = ['body'=>[],'url'=>'/v1/oauth2/token','method'=>'POST'];
                return false;
            }

            $newAccessToken = json_decode($response->getBody(),true);

            $this->accessToken = $newAccessToken['access_token'];
            $this->expires = time() + $newAccessToken['expires_in'];

            $this->accessToken = new AccessToken([
                'access_token' => $this->accessToken,
                'expires' => $this->expires,
                'vales' => ['type'=>'bearer']
            ]);
        }
    }

    // Create Order
    // /v2/checkout/orders
    public function createOrder($body) {
        $options = [];
        $options['body']  = json_encode( $body );
        $options['headers']['Content-Type'] = 'application/json';
        $request = $this->provider->getAuthenticatedRequest(
            'POST',
            $this->url.'/v2/checkout/orders',
            $this->accessToken,
            $options
        );

        try {
            $response = $this->provider->getResponse( $request );
        } catch ( BadResponseException $e ) {
            $this->error = print_r($e,true);//json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['token'=>$this->accessToken,'body'=>$body,'url'=>'/v2/checkout/orders','method'=>'POST'];
            return false;
        }

        return json_decode($response->getBody(),true);
    }


    // Show Capture Details (CapturesGetRequest)
    // /v2/payments/captures/{capture_id}
    public function getCapture($capture_id) {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $this->url.'/v2/payments/captures/'.$capture_id,
            $this->accessToken
        );

        try {
            $response = $this->provider->getResponse( $request );
        } catch ( BadResponseException $e ) {
            $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['body'=>[],'url'=>'/v2/payments/captures/'.$capture_id,'method'=>'GET'];
            return false;
        }

        return json_decode($response->getBody(),true);
    }

    // Get Order
    // /v2/checkout/orders/{id}
    public function getOrder($orderId) {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $this->url.'/v2/checkout/orders/'.$orderId,
            $this->accessToken
        );

        try {
            $response = $this->provider->getResponse( $request );
        } catch ( BadResponseException $e ) {
            $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['body'=>[],'url'=>'/v2/checkout/orders/'.$orderId,'method'=>'GET'];
            return false;
        }

        return json_decode($response->getBody(),true);
    }

    public function listPlans() {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $this->url.'/v1/billing/plans',
            $this->accessToken
        );

        try {
            $response = $this->provider->getResponse( $request );
        } catch ( BadResponseException $e ) {
            $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['body'=>[],'url'=>'/v1/billing/plans','method'=>'GET'];
            return false;
        }

        $body = json_decode($response->getBody(),true);

        return $body['plans'];
    }

    public function getPlan($subId) {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $this->url.'/v1/billing/plans/'.$subId,
            $this->accessToken
        );

        try {
            $response = $this->provider->getResponse( $request );
        } catch ( BadResponseException $e ) {
            $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['body'=>[],'url'=>'/v1/billing/plans'.$subId,'method'=>'GET'];
            return false;
        }

        $body = json_decode($response->getBody(),true);

        return $body;
    }

    public function getSubscription($subId) {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $this->url.'/v1/billing/subscriptions/'.$subId,
            $this->accessToken
        );

        try {
            $response = $this->provider->getResponse( $request );
        } catch ( BadResponseException $e ) {
            $this->error = json_decode( (string) $e->getResponse()->getBody(), true );
            $this->errorRequest = ['body'=>[],'url'=>'/v1/billing/subscriptions'.$subId,'method'=>'GET'];
            return false;
        }

        $body = json_decode($response->getBody(),true);

        return $body;
    }
}