<?php

namespace App\Http\Middleware;

use BadMethodCallException;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class CheckClientCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $oauthData = $this->validateAuthorization($request);

            if(in_array('auth', $guards)){
                if($oauthData && isset($oauthData->user) && $oauthData->user){
                    $user = new GenericUser((array) $oauthData->user);

                    app('auth')->guard('api')->setUser($user);
                    app('auth')->shouldUse('api');

                } else {
                    throw new \Exception('Missing user data');
                }
            }

            return $next($request);

        } catch (\Exception $e) {

        }

        throw new AuthenticationException;
    }

    public function validateAuthorization(Request $request)
    {
        if ($request->hasHeader('authorization') === false) {
            throw new \Exception('Missing "Authorization" header');
        }

        $header = $request->header('authorization');
        $jwt = trim((string) preg_replace('/^(?:\s+)?Bearer\s/', '', $header));

        // Attempt to parse and validate the JWT
        $token = (new Parser())->parse($jwt);
        $clientId = $token->getClaim('aud');

        try {
            switch($clientId){
                case config('app.api_gateway_client_id'):
                    $clientSecret = config('app.api_gateway_client_secret');
                    break;
                case config('app.api_platform_client_id'):
                    $clientSecret = config('app.api_platform_client_secret');
                    break;
                case config('app.api_tasklist_client_id'):
                    $clientSecret = config('app.api_tasklist_client_secret');
                    break;
                case config('app.api_crm_client_id'):
                    $clientSecret = config('app.api_crm_client_secret');
                    break;
                case config('app.api_org_client_id'):
                    $clientSecret = config('app.api_org_client_secret');
                    break;
                default:
                    $clientSecret = '';
                    break;
            }

            if ($token->verify(new Sha256(), $clientSecret) === false) {
                throw new \Exception('Access token could not be verified');
            }
        } catch (BadMethodCallException $exception) {
            throw new \Exception('Access token is not signed');
        }

        // Ensure access token hasn't expired
        $data = new ValidationData();
        $data->setCurrentTime(time());

        if ($token->validate($data) === false) {
            throw new \Exception('Access token is invalid');
        }

        $oauthData = $token->getClaim('data');

        // Return the request with additional attributes
        $request->merge([
            'oauth_access_token_id' => $token->getClaim('jti'),
            'oauth_client_id' => $clientId,
            'oauth_user_id' => $token->getClaim('sub')
        ]);

        return $oauthData;
    }
}
