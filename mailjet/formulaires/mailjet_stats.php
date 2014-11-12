<?php
/*
 * Plugin Mailjet 2
 * (c) 2009-2011 Collectif SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function mailjet_get_api_token()
{
    $key_cache = unserialize($GLOBALS['meta']['mailjet_api_authenticate_cache']);

    if(isset($key_cache[$_SERVER['REMOTE_ADDR']]) && $key_cache[$_SERVER['REMOTE_ADDR']]['timestamp']  > time()-10) {
        return $key_cache[$_SERVER['REMOTE_ADDR']]['token'];
    } else {

        $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);
        $params = array(
            'allowed_access' => array('campaigns','stats'),
            'method' => 'POST',
            'apikey' => $GLOBALS['meta']['mailjet_smtp_username'], // Use any API Key from your Sub-accounts
            'default_page' => 'stats',
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

function formulaires_mailjet_stats_charger_dist($list_id)
{
    $token = mailjet_get_api_token();
    if($token) {
        return array(
            'iframe_src' => 'https://www.mailjet.com/stats?t='.$token,
        );
    }
    return array();
}

function formulaires_mailjet_stats_verifier_dist(){
	return array();
}

function formulaires_mailjet_stats_traiter_dist(){

	return array();
}

