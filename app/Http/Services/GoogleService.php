<?php

namespace App\Http\Services;

use App\Models\User;
use Google\Service\Oauth2;

class GoogleService
{
    protected $user;
    protected $client;
    public function __construct($user)
    {
        $this->client = $this->google_client_config();
        $this->user = $user;
    }

    private function google_client_config()
    {
        $redirectURL = "user.integration.authorize_google_calendar";
        $all_scopes = implode(' ', array(
            \Google_Service_Calendar::CALENDAR,
            Oauth2::USERINFO_PROFILE,
            Oauth2::USERINFO_EMAIL
        ));
        $client = new \Google_Client();
        $client->setApplicationName('Events');
        $client->setScopes($all_scopes);
        $client->setAuthConfig(storage_path('app/googleClient/client_secret.json'));
        $client->setState('gcalendar');
        $client->setRedirectUri(route($redirectURL));
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        return $client;
    }

    public function authUrl()
    {
        $client = $this->client;
        return $auth_url = $client->createAuthUrl();
    }
}