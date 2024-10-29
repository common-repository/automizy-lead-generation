<?php
namespace Automizy;

use Guzzle\Http\Client;

class AutomizyApp
{
    const API_URL = "https://api.automizy.com/";
    const APP_ID = "sg8RPjvg7jaacAC51cz2";
    const APP_SECRET = "TvVmbM3ispVu8ztOX5Qo";

    private $id;
    private $self_url;

    public function __construct()
    {
        $this->id      = get_option('automizy_id');
        $this->self_url = admin_url('admin.php?page=automizy-plugin-settings');

        // adding the automizy analytics script to the <head> section on every page
        add_action('wp_head', array($this, 'append_script_to_header'));
        // adding the plugin settings menu to te admin area
        add_action('admin_menu', array($this, 'build_admin_menu'));
        // registering the settings we want to store in WP
        add_action('admin_init', array($this, 'plugin_settings'));
    }

    public function append_script_to_header()
    {
        if ($this->is_connected()) {
            echo <<<HTML
<!-- automizy lead generation -->
<script data-automizy-id="{$this->id}" src="//analytics.automizy.com/analytics.js" async></script>
<!-- /automizy lead generation -->
HTML;
        }
    }

    private function is_connected()
    {
        return !empty($this->id);
    }

    public function build_admin_menu()
    {
        add_menu_page('Automizy Plugin Settings', 'Automizy', 'administrator', 'automizy-plugin-settings', array($this, 'build_admin_settings_page'), 'dashicons-admin-generic');
    }

    public function plugin_settings()
    {
        // register option parameters for automizy plugin
        register_setting('automizy-plugin-settings-group', 'automizy_id');
        register_setting('automizy-plugin-settings-group', 'automizy_client_id');
        register_setting('automizy-plugin-settings-group', 'automizy_client_secret');

        // add the registered parameters to WP database if do not exist
        add_option('automizy_id', '', '', 'yes');
        add_option('automizy_client_id', '', '', 'yes');
        add_option('automizy_client_secret', '', '', 'yes');
    }

    public function build_admin_settings_page()
    {
        // if got response code from automizy API
        if (isset($_GET['code'])) {
            $this->id = $this->request_id($_GET['code']);
        }
        echo <<<HTML
<div class="wrap">

    <h2>Automizy settings</h2>
HTML;

        if ($this->is_connected()) {
            $this->display_help_message();
        } else {
            $this->display_login_button();
        }
        echo "</div>";
    }

    private function request_id($authCode)
    {
	    $client = new Client(self::API_URL);

        $request = $client->post('/oauth', null, array(
            'app_id' => self::APP_ID,
            'app_secret' => self::APP_SECRET,
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->self_url,
        ));

        $response = $request->send();
        $result   = $response->json();

        $account_request  = $client->get('/account', null, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $result['access_token'],
                'Accept' => 'application/json',
            )
        ));
        $account_response = $account_request->send();
        $account          = $account_response->json();
		
        update_option('automizy_id', $account['hash']);
        update_option('automizy_client_id', $result['client_id']);
        update_option('automizy_client_secret', $result['client_secret']);

        return $account['hash'];
    }

    private function display_help_message()
    {
        $example_img_src = plugins_url('images/capture-module-sample.gif', dirname(dirname(__FILE__)));

        echo <<<HTML
<div style="background: #fff; margin: 20px 0 0 0; padding: 20px 20px; border-left: solid 5px #669138; font: 13px Verdana, Arial, fans-serif;">
    Wordpress is <span style="color:#669138;"><b>connected</b></span> to your Automizy account<br/><br/>
    <b>Your Automizy ID:</b> {$this->id}<br/><br/>
    <span style="color:#669138;"><b>The Automizy analytics code is included to your website's source code.</b></span>
</div>
<div style="background: #fff; margin: 20px 0 0 0; padding: 20px 20px; border-left: solid 5px #eb880e; font: 13px Verdana, Arial, fans-serif;">
    <h3>To setup lead capture forms: open your website and click on Automizy icon as you can see below.</h3>
    <img src="$example_img_src" style="border: solid 1px #eb880e;">
</div>
HTML;
    }

    private function display_login_button()
    {
        $api_submit_url = $this->build_submit_url();

        echo <<<HTML
<!-- automizy login -->
<div
    style="background: #fff; margin: 20px 0 0 0; padding: 20px 20px; border-left: solid 5px #eb880e; font: 13px Verdana, Arial, fans-serif;">
    <h3>You have to connect your WordPress website with your Automizy account to use this plugin.</h3>
</div>
<br/><br/>
<a id="sign-in-with-automizy" href="$api_submit_url">Connect Wordpress with my Automizy account</a>
<style>
    #sign-in-with-automizy {
        -moz-box-shadow: inset 0px -3px 0px 0px #d56224;
        -webkit-box-shadow: inset 0px -3px 0px 0px #d56224;
        box-shadow: inset 0px -3px 0px 0px #d56224;
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f79764), color-stop(1, #f47e3e));
        background: -moz-linear-gradient(center top, #f79764 5%, #f47e3e 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#f79764", endColorstr="#f47e3e");
        background-color: #f79764;
        text-shadow: 1px 2px 1px #ca6e3d;
        box-sizing: border-box;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        text-indent: 0px;
        display: inline-block;
        color: #ffffff;
        font-family: Arial;
        font-size: 12px;
        font-weight: bold;
        font-style: normal;
        padding: 8px 20px 9px 20px;
        text-decoration: none;
        text-align: center;
        margin: 0 0 0 0;
        min-width: 100px;
    }

    #sign-in-with-automizy:hover {
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f47e3e), color-stop(1, #f79764));
        background: -moz-linear-gradient(center top, #f47e3e 5%, #f79764 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#f47e3e", endColorstr="#f79764");
        background-color: #f47e3e;
    }

    #sign-in-with-automizy:active {
        position: relative;
        top: 1px;
        -moz-box-shadow: inset 0px -2px 0px 0px #d56224;
        -webkit-box-shadow: inset 0px -2px 0px 0px #d56224;
        box-shadow: inset 0px -2px 0px 0px #d56224;
    }
</style>
<script>
    document.getElementById('sign-in-with-automizy').onclick = function () {
        location.href = this.href;
        return false;
    }
</script>
<!-- /automizy login -->
HTML;
    }

    private function build_submit_url()
    {
        return self::API_URL . 'oauth/authorize?' . http_build_query(array(
            'redirect_uri' => $this->self_url,
            'app_id' => self::APP_ID,
            'app_secret' => self::APP_SECRET,
        ));
    }
}