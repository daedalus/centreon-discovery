<?php

$filepath = '@CENTREON_ETC@/centreon.conf.php';
//$filepath = '/etc/centreon/centreon.conf.php';

$agentDir = "@AGENT_DIR@/DiscoveryAgent_central.py";
//$agentDir = "/etc/centreon-discovery/DiscoveryAgent_central.py";

if (file_exists($filepath)) {
	include($filepath);
	$connect = mysql_connect($conf_centreon['hostCentreon'], $conf_centreon['user'], $conf_centreon['password']) or die('Impossible de se connecter � la base de donnees'.mysql_error());
	mysql_select_db($conf_centreon['db']) or die('Impossible de trouver la base de donnees '.mysql_error());
	//On met � jour le nagios_server_id dans la bdd
	mysql_query('UPDATE mod_discovery_rangeip SET nagios_server_id='.(int)$_POST['poller_id'].',poller_status=0 WHERE id='.(int)$_POST['id'].';') or die ('Erreur : '.mysql_error());
	
	//On recherche le chemin du fichier dans la bdd, car les chemins relatifs ne fonctionnent pas avec Ajax
	$sql = mysql_query("SELECT value FROM options WHERE options.key='oreon_path';");
	$currentpath = mysql_fetch_row($sql);
		
	//On relance le script python afin de v�rifier le status du nouveau Discovery-Agent poller
	if (file_exists($agentDir)) {
//		shell_exec('python '.$agentDir.' STATUS_POLLER > /dev/null 2>&1 &');
		shell_exec('python '.$agentDir.' STATUS_POLLER '.(int)$_POST['poller_id'].' >> /tmp/agent_central.log 2>&1 &');
	}
	else {shell_exec('python '.$currentpath[0].'/www/modules/Discovery/include/agent/DiscoveryAgent_central.py STATUS_POLLER > /dev/null 2>&1 &');}
}
?>