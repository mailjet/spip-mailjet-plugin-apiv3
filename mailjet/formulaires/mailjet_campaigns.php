<?php
/*
 * Plugin Mailjet 1.2
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function mailjet_get_api_token()
{
    $key_cache = unserialize($GLOBALS['meta']['mailjet_api_authenticate_cache']);

    if(isset($key_cache[$_SERVER['REMOTE_ADDR']]) && $key_cache[$_SERVER['REMOTE_ADDR']]['timestamp']  > time()-3600) {
        return $key_cache[$_SERVER['REMOTE_ADDR']]['token'];
    } else {

        $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);
        $params = array(
            'allowed_access' => array('campaigns','contacts','stats','preferences'),
            'method' => 'POST',
            'apikey' => $GLOBALS['meta']['mailjet_smtp_username'], // Use any API Key from your Sub-accounts
            'default_page' => 'campaigns',
            'lang' => $GLOBALS['spip_lang'],
            'type' => 'page',
        );




        $response = $api->apiKeyAuthenticate($params);

        if ($response->status == 'OK') {
            $token = $response->token;
            //TODO add token to meta mailjet_api_authenticate_cache
            $key_cache[$_SERVER['REMOTE_ADDR']]['timestamp'] = time();
            $key_cache[$_SERVER['REMOTE_ADDR']]['token'] = $token;

            ecrire_meta('mailjet_api_authenticate_cache', serialize($key_cache));
            return $token;
        }
        return false;
    }
}

function formulaires_mailjet_campaigns_charger_dist($list_id)
{
    $token = mailjet_get_api_token();
    if($token) {
        return array(
            'iframe_src' => 'https://www.mailjet.com/campaigns?t='.$token,
        );
    }
    return array();
}

function formulaires_mailjet_campaigns_verifier_dist(){
	return array();
}

function formulaires_mailjet_campaigns_traiter_dist(){

	return array();
}

