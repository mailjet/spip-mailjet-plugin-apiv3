<?php
/*
 * Plugin Mailjet 2
 * (c) 2009-2011 Collectif SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function formulaires_configurer_mailjet_charger_dist(){
	$valeurs = array(
		'mailjet_adresse_envoi' => $GLOBALS['meta']['mailjet_adresse_envoi'],
		'mailjet_adresse_envoi_nom' => $GLOBALS['meta']['mailjet_adresse_envoi_nom'],
		'mailjet_adresse_envoi_email' => $GLOBALS['meta']['mailjet_adresse_envoi_email'],
		'mailjet_smtp' => $GLOBALS['meta']['mailjet_smtp'],
		'mailjet_smtp_host' => $GLOBALS['meta']['mailjet_smtp_host'],
		'mailjet_smtp_port' => $GLOBALS['meta']['mailjet_smtp_port']?$GLOBALS['meta']['mailjet_smtp_port']:'25',
		'mailjet_smtp_auth' => $GLOBALS['meta']['mailjet_smtp_auth'],
		'mailjet_smtp_username' => $GLOBALS['meta']['mailjet_smtp_username'],
		'mailjet_smtp_password' => $GLOBALS['meta']['mailjet_smtp_password'],
		'mailjet_smtp_secure' => $GLOBALS['meta']['mailjet_smtp_secure'],
		'mailjet_smtp_sender' => $GLOBALS['meta']['mailjet_smtp_sender'],
		'mailjet_filtre_images' => $GLOBALS['meta']['mailjet_filtre_images'],
		'mailjet_filtre_iso_8859' => $GLOBALS['meta']['mailjet_filtre_iso_8859'],
		'_enable_smtp_secure' => (intval(phpversion()) == 5)?' ':'',
		'mailjet_cc' => $GLOBALS['meta']['mailjet_cc'],
		'mailjet_bcc' => $GLOBALS['meta']['mailjet_bcc'],
	'tester' => '',
	);

	return $valeurs;
}

function formulaires_configurer_mailjet_verifier_dist(){
	$erreurs = array();
	if ($email = _request('mailjet_adresse_envoi_email')
	  AND !email_valide($email)) {
		$erreurs['mailjet_adresse_envoi_email'] = _T('form_email_non_valide');
		set_request('mailjet_adresse_envoi','oui');
	}
	if (_request('mailjet_smtp')=='oui'){
		if (!($h=_request('mailjet_smtp_host')))
			$erreurs['mailjet_smtp_host'] = _T('info_obligatoire');
		else {
			$regexp_ip_valide = '#^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))|((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$#'; 
			// Source : http://www.d-sites.com/2008/10/09/regex-ipv4-et-ipv6/
			if (!preg_match($regexp_ip_valide,$h)){ // ce n'est pas une IP
				if(!preg_match(';^([^.\s/?:]+[.]){0,2}[^.\s/?:]+$;',$h)
				  OR gethostbyname($h)==$h)
					$erreurs['mailjet_smtp_host'] = _T('mailjet:erreur_invalid_host');
			}
			else {
				if (gethostbyaddr($h)==$h)
					$erreurs['mailjet_smtp_host'] = _T('mailjet:erreur_invalid_host');
			}
		}
		if (!($p=_request('mailjet_smtp_port')))
			$erreurs['mailjet_smtp_port'] = _T('info_obligatoire');
		elseif(!preg_match(';^[0-9]+$;',$p) OR !intval($p))
			$erreurs['mailjet_smtp_port'] = _T('mailjet:erreur_invalid_port');

		if (!_request('mailjet_smtp_auth'))
			$erreurs['mailjet_smtp_auth'] = _T('info_obligatoire');

		if (_request('mailjet_smtp_auth')=='oui'){
			if (!_request('mailjet_smtp_username'))
				$erreurs['mailjet_smtp_username'] = _T('info_obligatoire');
			if (!_request('mailjet_smtp_password'))
				$erreurs['mailjet_smtp_password'] = _T('info_obligatoire');
		}
	}
	if ($emailcc = _request('mailjet_cc')
	  AND !email_valide($emailcc)) {
		$erreurs['mailjet_cc'] = _T('form_email_non_valide');
	}
	if ($emailbcc = _request('mailjet_bcc')
	  AND !email_valide($emailbcc)) {
		$erreurs['mailjet_bcc'] = _T('form_email_non_valide');
	}
	
	if(count($erreurs)>0){
		$erreurs['message_erreur'] = _T('mailjet:erreur_generale');
	}
    /*
     * Mailjet API keys validation
     */
    $MailjetApi = new SPIP_Mailjet_Api(_request('mailjet_smtp_username'), _request('mailjet_smtp_password'));
    if($MailjetApi->getContext() == false) {
        effacer_meta('mailjet_smtp_username');
		effacer_meta('mailjet_smtp_password');
		effacer_meta('mailjet_api_authenticate_cache');
        $erreurs['message_erreur'] = _T('mailjet:mailjet_api_auth_error');
		$erreurs['mailjet_smtp_username'] = ' ';
        $erreurs['mailjet_smtp_password'] = ' ';
	} else {
        /*
         * Validates entered email address if is one of active sender addresses of current API account
         * If "Use the site's settings" radio option is selected for "Sender's address configuration" this check is omitted
         */
        if (_request('mailjet_adresse_envoi') == 'oui') {
            $email = _request('mailjet_adresse_envoi_email');
            $validEmail = $MailjetApi->validateSenderEmail(array('email' => $email));
            if (!$validEmail) {
                $erreurs['mailjet_adresse_envoi_email'] = _T('form_email_non_valide');
	}
        }
    }
	return $erreurs;
}

function formulaires_configurer_mailjet_traiter_dist(){
	include_spip('inc/meta');

	$mailjet_adresse_envoi = _request('mailjet_adresse_envoi');
	ecrire_meta('mailjet_adresse_envoi', ($mailjet_adresse_envoi=='oui')?'oui':'non');

	$mailjet_adresse_envoi_nom = _request('mailjet_adresse_envoi_nom');
	ecrire_meta('mailjet_adresse_envoi_nom', $mailjet_adresse_envoi_nom?$mailjet_adresse_envoi_nom:'');

	$mailjet_adresse_envoi_email = _request('mailjet_adresse_envoi_email');
	ecrire_meta('mailjet_adresse_envoi_email', $mailjet_adresse_envoi_email?$mailjet_adresse_envoi_email:'');

	$mailjet_smtp = _request('mailjet_smtp');
	ecrire_meta('mailjet_smtp', ($mailjet_smtp=='oui')?'oui':'non');

	$mailjet_smtp_host = _request('mailjet_smtp_host');
	ecrire_meta('mailjet_smtp_host', $mailjet_smtp_host?$mailjet_smtp_host:'');

	$mailjet_smtp_port = _request('mailjet_smtp_port');
	ecrire_meta('mailjet_smtp_port', strlen($mailjet_smtp_port)?intval($mailjet_smtp_port):'');

	$mailjet_smtp_auth = _request('mailjet_smtp_auth');
	ecrire_meta('mailjet_smtp_auth', ($mailjet_smtp_auth=='oui')?'oui':'non');

	$mailjet_smtp_username = _request('mailjet_smtp_username');
	ecrire_meta('mailjet_smtp_username', $mailjet_smtp_username);

	$mailjet_smtp_password = _request('mailjet_smtp_password');
	ecrire_meta('mailjet_smtp_password', $mailjet_smtp_password);

	if (intval(phpversion()) == 5) {
		$mailjet_smtp_secure = _request('mailjet_smtp_secure');
		ecrire_meta('mailjet_smtp_secure', in_array($mailjet_smtp_secure,array('non','ssl','tls'))?$mailjet_smtp_secure:'non');
	}

	$mailjet_smtp_sender = _request('mailjet_smtp_sender');
	ecrire_meta('mailjet_smtp_sender', $mailjet_smtp_sender);

	ecrire_meta('mailjet_filtre_images', intval(_request('mailjet_filtre_images')));
	ecrire_meta('mailjet_filtre_iso_8859', intval(_request('mailjet_filtre_iso_8859')));

	$mailjet_cc = _request('mailjet_cc');
	ecrire_meta('mailjet_cc', $mailjet_cc?$mailjet_cc:'');

	$mailjet_bcc = _request('mailjet_bcc');
	ecrire_meta('mailjet_bcc', $mailjet_bcc?$mailjet_bcc:'');
	
	
	$res = array('message_ok'=>_T('mailjet:config_info_enregistree'));

	// faut-il envoyer un message de test ?
	if (_request('tester')){

		if ($GLOBALS['meta']['mailjet_adresse_envoi'] == 'oui'
		  AND $GLOBALS['meta']['mailjet_adresse_envoi_email'])
			$destinataire = $GLOBALS['meta']['mailjet_adresse_envoi_email'];
		else
			$destinataire = $GLOBALS['meta']['email_webmaster'];

		if ((mailjet_envoyer_mail_test($destinataire,_T('mailjet:corps_email_de_test')))===true){
			// OK
			$res = array('message_ok'=>_T('mailjet:email_test_envoye'));
		}
		else {
			// erreur
			$res = array('message_erreur'=>_T('mailjet:erreur')._T('mailjet:erreur_dans_log'));
		}
	}
	
	return $res;
}

function mailjet_envoyer_mail_test($destinataire,$titre){
	include_spip('classes/mailjet');
	$message_html	= recuperer_fond('emails/test_email_html', array());
	$message_texte	= recuperer_fond('emails/test_email_texte', array());

	// passer par envoyer_mail pour bien passer par les pipeline et avoir tous les logs
	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	$retour = $envoyer_mail($destinataire, $titre, array('html'=>$message_html,'texte'=>$message_texte));

	return $retour?true:false;
}
?>
