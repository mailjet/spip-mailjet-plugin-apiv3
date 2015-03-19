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

        $MailjetApi = new SPIP_Mailjet_Api($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);

        $params = array(
            'allowed_access' => array('campaigns','contacts','stats','preferences'),
            'method' => 'POST',
            'apikey' => $GLOBALS['meta']['mailjet_smtp_username'], // Use any API Key from your Sub-accounts
            'default_page' => 'contacts',
            'lang' => $GLOBALS['spip_lang'],
            'type' => 'page',
        );

        $response = $MailjetApi->getToken($params);

        if ($response->status == 'OK') {
            $token = $response->token;
            //TODO add token to meta mailjet_api_authenticate_cache
            $key_cache[$_SERVER['REMOTE_ADDR']]['timestamp'] = time();
            $key_cache[$_SERVER['REMOTE_ADDR']]['token'] = $token;

            ecrire_meta('mailjet_api_authenticate_cache', serialize($key_cache));
            return $token;
        }
        echo '<p class="error">'._T('mailjet:mailjet_api_auth_error').'</p>';
        return false;
    }
}

function formulaires_mailjet_lists_charger_dist()
{
    $token = mailjet_get_api_token();
    $locale = mailjet_get_iframe_lang($GLOBALS['spip_lang']);
    if($token) {
        return array(
            'iframe_src' => 'https://www.mailjet.com/contacts?t='.$token.'&locale='.$locale,
        );
    }
    return array();
}

function formulaires_mailjet_lists_verifier_dist(){
	return array();
}

function formulaires_mailjet_lists_traiter_dist(){

	return array();
}



?>
