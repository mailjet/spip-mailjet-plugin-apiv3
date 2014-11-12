<?php


if (!defined("_ECRIRE_INC_VERSION")) return;

function formulaires_create_list_charger_dist(){

    $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);
    $statistics = '';
    if ($list_id = _request('list_id')) { //editing an existing list, show contact form

        $params = array(
            'id' => $list_id,
        );
        $response = $api->listsStatistics($params);

        if($response->status == 'OK') {
            $statistics = (array) $response->statistics;
        }

        $params = array(
            'id' => $list_id,
        );
        $response = $api->listsContacts($params);
        $contacts = array();
        $count = 0;
        if($response->status == 'OK'){
            $contacts = array_map(create_function('$el', 'return (array) $el;'), $response->result);
            $count = $response->total_cnt;
        }

        return array(
            'list' => $statistics,
            'save_button_label' => _T('mailjet:save_list_button_label'),
            'contacts' => $contacts,
            'count' => $count,
        );

    }

	return array(
        'save_button_label' => _T('mailjet:create_list_button_label'),
        'list' => null,
    );
}

function formulaires_create_list_verifier_dist(){
	$erreurs = array();
    if(_request('save') == _T('mailjet:save_list_button_label') ||_request('save') == _T('mailjet:create_list_button_label')){
        if ($name = _request('mailjet_list_name') AND !preg_match('/[a-zA-Z0-9]+/', $name)) {
            $erreurs['mailjet_list_name'] = _T('mailjet:list_name_alfanumeric');
        }

        if( !_request('mailjet_list_label')){
            $erreurs['mailjet_list_label'] = _T('mailjet:list_label_required');
        }
    }
	if(count($erreurs)>0){
		$erreurs['message_erreur'] = _T('mailjet:create_list_error');
	}
	return $erreurs;
}

function formulaires_create_list_traiter_dist(){

	include_spip('inc/meta');

    $api = new MailjetApi($GLOBALS['meta']['mailjet_smtp_username'], $GLOBALS['meta']['mailjet_smtp_password']);
    if(_request('list_id')){
        if(_request('save') == _T('mailjet:save_list_button_label') ||_request('save') == _T('mailjet:create_list_button_label')){
        //TODO save list

         //editing an existing list
            $params = array(
                'method' => 'POST',
                'id' => _request('list_id'),
                'label' => _request('mailjet_list_label'),
                'name' => _request('mailjet_list_name'),
            );
            $response = $api->listsUpdate($params);
            if($response->status == 'OK'){
                return array('message_ok'=> sprintf(_T('mailjet:list_updated'), _request('mailjet_list_label')));
            } else{
                return array('message_erreur' => sprintf(_T('mailjet:error_list_not_updated'), _request('mailjet_list_label')));
            }

        }elseif(_request('save') == _T('mailjet:delete_selected_contacts')) {
            //Delete selected contacts
            $contacts = join(',', _request('contacts'));

            $params = array(
                'method' => 'POST',
                'contacts' => $contacts,
                'id' => _request('list_id'),
            );
            $response = $api->listsRemoveManyContacts($params);

            if($response->status == 'OK'){
                return array('message_ok'=>_T('mailjet:selected_contacts_deleted'));
            } else{
                return array('message_erreur'=>_T('mailjet:error_selected_contacts_not_deleted'));
            }

        }elseif(_request('save') == _T('mailjet:add_contact_button_text')) {
            //Delete selected contacts
            $contacts = join(',', _request('mailjet_list_contact_email'));

            $params = array(
                'method' => 'POST',
                'contacts' => $contacts,
                'id' => _request('list_id'),
            );
            $response = $api->listsAddManyContacts($params);

            if($response->status == 'OK'){
                return array('message_ok'=>_T('mailjet:selected_contacts_added'));
            } else{
                return array('message_erreur'=>_T('mailjet:error_selected_contacts_not_added'));
            }

        }
    }else{
        $params = array(
            'method' => 'POST',
            'label' => _request('mailjet_list_label'),
            'name' => _request('mailjet_list_name'),
        );
        $response = $api->listsCreate($params);

        if($response->status == 'OK'){
            return array('message_ok' => sprintf(_T('mailjet:list_created'), _request('mailjet_list_label')));
        } else{
            return array('message_erreur' => sprintf(_T('mailjet:error_list_not_created'), _request('mailjet_list_label')));
        }
    }
}

?>
