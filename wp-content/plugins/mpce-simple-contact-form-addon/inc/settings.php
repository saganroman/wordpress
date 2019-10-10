<?php
if (!defined('ABSPATH')) exit;


function mpceCFAInitGlobalSettings() {
	static $inited = false;
	if (!$inited ) {
		$inited = true;

		define('MPCE_CFA_PLUGIN_NAME', 'mpce-simple-contact-form-addon');
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $pluginData = get_plugin_data(MPCE_CFA_PLUGIN_DIR . MPCE_CFA_PLUGIN_NAME . '.php', false, false);

//		define('MPCE_CFA_TEXTDOMAIN', 'mpce-cfa');
		define('MPCE_CFA_PLUGIN_SHORT_NAME', 'mpce-cfa');
		define('MPCE_CFA_PLUGIN_DIR_NAME', basename(dirname(MPCE_CFA_PLUGIN_FILE)));
		define('MPCE_CFA_PLUGIN_DIR_URL', plugin_dir_url(MPCE_CFA_PLUGIN_DIR_NAME . '/' . basename(MPCE_CFA_PLUGIN_FILE)));

		define('MPCE_CFA_VERSION', $pluginData['Version']);
		define('MPCE_CFA_AUTHOR', $pluginData['Author']);

		//define('MPCE_CFA_LICENSE_TYPE', 'Personal');
		define('MPCE_CFA_EDD_STORE_URL', $pluginData['PluginURI']);
		define('MPCE_CFA_EDD_ITEM_NAME', $pluginData['Name']/* . ' ' . MPCE_CFA_LICENSE_TYPE*/);
		define('MPCE_CFA_EDD_ITEM_ID', 265303);
		define('MPCE_CFA_RENEW_URL', $pluginData['PluginURI'] . 'buy/');

        global $cfaLicense;
        $cfaLicense = new CFALicense();
	}
}
mpceCFAInitGlobalSettings();