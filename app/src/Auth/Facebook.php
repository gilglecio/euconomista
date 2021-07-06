<?php 

namespace App\Auth;

use Facebook\Facebook as Fb;

class Facebook
{
	const APP_ID = '1308173232559152';
    const APP_SECRET = 'db4945b1af458088ca290103ec836029';

    public static function getFB()
    {
        return new Fb([
            'app_id' => self::APP_ID,
            'app_secret' => self::APP_SECRET,
            'default_graph_version' => 'v2.2',
        ]);
    }

    public static function getLoginUrl()
    {
        $fb = self::getFB();
        $helper = $fb->getRedirectLoginHelper();

        return $helper->getLoginUrl(APP_URL . '/fb-callback', ['email']);
    }
}