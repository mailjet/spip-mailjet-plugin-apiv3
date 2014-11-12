<?php
/*
 * Plugin Mailjet 2
 * (c) 2009-2011 Collectif SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function formulaires_mailjet_subscribe_charger_dist($list_id){

    $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);

    $params = array(
        'id' => $list_id,
    );

    $response = $api->listsStatistics($params);


    if ($response->status == 'OK') {
        $list = $response->statistics;
        return array(
            'legend' => sprintf(_T('mailjet:subscribe_form_legend'), $list->label),
            'list_id' => $response->statistics->id,
            'list_label' => $response->statistics->label,
        );
    }
    return array();
}

function formulaires_mailjet_subscribe_verifier_dist(){
	$erreurs = array();

    if(!_request('mailjet_subscribe_email')) {
        $erreurs['mailjet_subscribe_email'] = _T('mailjet:email_required');
    }

    if ($email = _request('mailjet_subscribe_email')
        AND !email_valide($email)) {
        $erreurs['mailjet_subscribe_email'] = _T('form_email_non_valide');
    }
	if(count($erreurs)>0){
		$erreurs['message_erreur'] = _T('mailjet:erreur_generale');
	}
	return $erreurs;
}

function formulaires_mailjet_subscribe_traiter_dist(){

	include_spip('inc/meta');

    //TODO delete selected lists

    $list_id = _request('mailjet_subscribe_list');
    $email = _request('mailjet_subscribe_email');

    $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);

    $success = true;

    $params = array(
        'method' => 'POST',
        'contact' => $email,
        'id' => $list_id,
    );
    $response = $api->listsAddContact($params);

    $success = $success && ($response->status == 'OK');



    if($success){
        $res = array('message_ok'=>_T('mailjet:thanks_for_subscribing'));
    } else{
        $res = array('message_erreur'=>_T('mailjet:could_not_subscribe'));
    }


	
	return $res;
}

?>
