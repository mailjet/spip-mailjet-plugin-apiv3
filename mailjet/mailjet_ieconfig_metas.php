<?php
/*
 * Plugin Mailjet 2
 * (c) 2009-2011 Collectif SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function mailjet_ieconfig_metas($table){
	$table['mailjet']['titre'] = _T('mailjet:configuration_mailjet');
	$table['mailjet']['icone'] = 'mailjet-16.png';
	$table['mailjet']['metas_brutes'] = 'mailjet_adresse_envoi,mailjet_adresse_envoi_nom,mailjet_adresse_envoi_email,mailjet_smtp,mailjet_smtp_host,mailjet_smtp_port,mailjet_smtp_auth,mailjet_smtp_username,mailjet_smtp_password,mailjet_smtp_secure,mailjet_smtp_sender,mailjet_filtre_images,mailjet_filtre_css,mailjet_filtre_iso_8859,mailjet_api_authenticate_cache';
	return $table;
}

