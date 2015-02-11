<?php
namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Description of EveOnlineSSOClient
 *
 * @author unti1x
 */
class EveOnlineSSO extends OAuth2 {

    public $id = 'eve-online-sso';
    public $name = 'EveOnline SSO';
    public $title = 'Eve Online';

    public $authUrl = 'https://login.eveonline.com/oauth/authorize';

    public $tokenUrl = 'https://login.eveonline.com/oauth/token';

    public $apiBaseUrl = 'https://login.eveonline.com/oauth';

    protected function generateAuthState()
    {
        return sha1(uniqid(get_class($this), true));
    }

    public function buildAuthUrl(array $params = [])
    {
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $params['state'] = $authState;

        return parent::buildAuthUrl($params);
    }


    /**
     * @inheritdoc
     */
    public function fetchAccessToken($authCode, array $params = [])
    {
        $authState = $this->getState('authState');
        if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
            throw new HttpException(400, 'Invalid auth state parameter.');
        } else {
            $this->removeState('authState');
        }

        return parent::fetchAccessToken($authCode, $params);
    }


    public function initUserAttributes() {
        $attributes = $this->api('verify', 'GET');
        return $attributes;
    }

    /**
     *
     * @param \yii\authclient\OAuthToken $accessToken
     * @param string  $url
     * @param string $method
     * @param array $params
     * @param array $headers
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers) {
        $headers[] = 'Authorization: Bearer ' . $accessToken->getToken();

        return $this->sendRequest($method, $url, $params, $headers);
    }

}
