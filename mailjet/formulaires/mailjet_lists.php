<?php
/*
 * Plugin Mailjet 2
 * (c) 2009-2011 Collectif SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function mailjet_object_to_array($ob)
{
    return (array) $ob;
}

function formulaires_mailjet_lists_charger_dist(){

    $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);

    $lists = $api->listsAll();

	$valeurs = array(
        'lists' => array_map('mailjet_object_to_array', $lists->lists),
	);

	return $valeurs;
}

function formulaires_mailjet_lists_verifier_dist(){
	$erreurs = array();

	if(count($erreurs)>0){
		$erreurs['message_erreur'] = _T('mailjet:erreur_generale');
	}
	return $erreurs;
}

function formulaires_mailjet_lists_traiter_dist(){

	include_spip('inc/meta');

    //TODO delete selected lists

    $list_ids = _request('lists');
    $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);

    $success = true;
    foreach ($list_ids as $list_id) {
        $params = array(
            'method' => 'POST',
            'id' => $list_id,
        );
        $response = $api->listsDelete($params);

        $success = $success && ($response->status == 'OK');

    }

    if($success){
        $res = array('message_ok'=>_T('mailjet:selected_lists_deleted'));
    } else{
        $res = array('message_erreur'=>_T('mailjet:error_selected_lists_not_deleted'));
    }


	
	return $res;
}

?>
