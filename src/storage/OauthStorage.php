<?php
/**
 * OauthStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @packages sweelix\oauth2\server\storage
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\OpenID\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\JwtAccessTokenInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\ScopeInterface;
use Yii;

/**
 * OauthStorage class
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @packages sweelix\oauth2\server\storage
 * @since XXX
 */
class OauthStorage implements
    AccessTokenInterface,
    AuthorizationCodeInterface,
    ClientCredentialsInterface,
    JwtAccessTokenInterface, // identical to AccessTokenInterface
    RefreshTokenInterface,
    ScopeInterface
{
    /**
     * @var string
     */
    private $accessTokenClass;

    /**
     * @var string
     */
    private $authCodeClass;

    /**
     * @var string
     */
    private $clientClass;

    /**
     * @var string
     */
    private $refreshTokenClass;

    /**
     * @var string
     */
    private $scopeClass;

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getAccessTokenClass()
    {
        if ($this->accessTokenClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            $this->accessTokenClass = get_class($client);
        }
        return $this->accessTokenClass;
    }

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getAuthCodeClass()
    {
        if ($this->authCodeClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
            $this->authCodeClass = get_class($client);
        }
        return $this->authCodeClass;
    }

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getClientClass()
    {
        if ($this->clientClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
            $this->clientClass = get_class($client);
        }
        return $this->clientClass;
    }

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getRefreshTokenClass()
    {
        if ($this->refreshTokenClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
            $this->refreshTokenClass = get_class($client);
        }
        return $this->refreshTokenClass;
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken($oauth_token)
    {
        $accessTokenClass = $this->getAccessTokenClass();
        $accessToken = $accessTokenClass::findOne($oauth_token);
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        if ($accessToken !== null) {
            $finalToken = [
                'expires' => $accessToken->expiry,
                'client_id' => $accessToken->clientId,
                'user_id' => $accessToken->userId,
                'scope' => implode(' ', $accessToken->scopes),
                'id_token' => $accessToken->id,
            ];
            $accessToken = $finalToken;
        }
        return $accessToken;
    }

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getScopeClass()
    {
        if ($this->scopeClass === null) {
            $scope = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
            $this->scopeClass = get_class($scope);
        }
        return $this->scopeClass;
    }

    /**
     * @inheritdoc
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        $accessToken->id = $oauth_token;
        $accessToken->clientId = $client_id;
        $accessToken->userId = $user_id;
        $accessToken->expiry = $expires;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $accessToken->scopes = $scopes;
        $accessToken->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function unsetAccessToken($access_token)
    {
        $accessTokenClass = $this->getAccessTokenClass();
        $accessToken = $accessTokenClass::findOne($access_token);
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        if ($accessToken !== null) {
            $accessToken->delete();
        }
        return true; //TODO: check why we should return true/false
    }

    /**
     * @inheritdoc
     */
    public function getAuthorizationCode($code)
    {
        $authCodeClass = $this->getAuthCodeClass();
        $authCode = $authCodeClass::findOne($code);
        if ($authCode !== null) {
            $finalCode = [
                'client_id' => $authCode->clientId,
                'user_id' => $authCode->userId,
                'expires' => $authCode->expiry,
                'redirect_uri' => $authCode->redirectUri,
                'scope' => implode(' ', $authCode->scopes),
                'id_token' => $authCode->tokenId,
            ];
            $authCode = $finalCode;
        }
        return $authCode;
    }

    /**
     * @inheritdoc
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        $authCode->id = $code;
        $authCode->clientId = $client_id;
        $authCode->userId = $user_id;
        $authCode->redirectUri = $redirect_uri;
        $authCode->expiry = $expires;
        $authCode->tokenId = $id_token;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $authCode->scopes = $scopes;
        $authCode->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function expireAuthorizationCode($code)
    {
        $authCodeClass = $this->getAuthCodeClass();
        $authCode = $authCodeClass::findOne($code);
        if ($authCode !== null) {
            $authCode->delete();
        }
        return true;
    }
    /**
     * @inheritdoc
     */
    public function getClientDetails($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        if ($client !== null) {
            $finalClient = [
                'redirect_uri' => $client->redirectUri,
                'client_id' => $client->id,
                'grant_types' => $client->grantTypes,
                'user_id' => $client->userId,
                'scope' => implode(' ', $client->scopes),
            ];
            $client = $finalClient;
        }
        return ($client !== null) ? $client : false;
    }

    /**
     * @inheritdoc
     */
    public function getClientScope($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        $scopes = '';
        if ($client !== null) {
            $scopes = implode(' ', $client->scopes);
        }
        return $scopes;
    }

    /**
     * @inheritdoc
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        $notRestricted = true;
        if ($client !== null) {
            if (empty($client->grantTypes) === false) {
                $notRestricted = in_array($grant_type, $client->grantTypes);
            }
        }
        return $notRestricted;
    }

    /**
     * @inheritdoc
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        return ($client !== null) ? ($client->secret === $client_secret) : false;
    }

    /**
     * @inheritdoc
     */
    public function isPublicClient($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        return ($client !== null) ? $client->isPublic : false;
    }

    /**
     * @inheritdoc
     */
    public function getRefreshToken($refresh_token)
    {
        $refreshTokenClass = $this->getRefreshTokenClass();
        $refreshToken = $refreshTokenClass::findOne($refresh_token);
        if ($refreshToken !== null) {
            $finalToken = [
                'refresh_token' => $refreshToken->id,
                'client_id' => $refreshToken->clientId,
                'user_id' => $refreshToken->userId,
                'expires' => $refreshToken->expiry,
                'scope' => implode(' ', $refreshToken->scopes),
            ];
            $refreshToken = $finalToken;
        }
        return $refreshToken;
    }

    /**
     * @inheritdoc
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        $refreshToken->id = $refresh_token;
        $refreshToken->clientId = $client_id;
        $refreshToken->userId = $user_id;
        $refreshToken->expiry = $expires;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $refreshToken->scopes = $scopes;
        $refreshToken->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function unsetRefreshToken($refresh_token)
    {
        $refreshTokenClass = $this->getRefreshTokenClass();
        $refreshToken = $refreshTokenClass::findOne($refresh_token);
        if ($refreshToken !== null) {
            $refreshToken->delete();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function scopeExists($scope)
    {
        $scopeClass = $this->getScopeClass();
        $availableScopes = $scopeClass::findAvailableScopeIds();
        $requestedScopes = explode(' ', $scope);
        $missingScopes = array_diff($requestedScopes, $availableScopes);
        return empty($missingScopes);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScope($client_id = null)
    {
        $scopeClass = $this->getScopeClass();
        $availableDefaultScopes = $scopeClass::findDefaultScopeIds($client_id);
        $scope = implode(' ', $availableDefaultScopes);
        if (empty($scope) === true) {
            $scope = null;
        }
        return $scope;
    }

}
