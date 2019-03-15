<?php

/*
Plugin Name: Internship API
Description: API modifications for fetching internships from Exchange Platform.
Author: Anton Levholm
Version: 1.0
Author URI: levholm.se

-- Exchange Platform API Documentation --
https://sites.google.com/iaeste.org/ep-user-guide/offers/exporting/api?authuser=0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_REST_Internships_Controller extends \WP_REST_Controller
{

    protected $namespace;
    protected $rest_base;
    protected $username;
    protected $password;
    protected $token;
    protected $company_id;
    protected $foreign_offers;

    public function __construct()
    {
        $this->namespace = 'internships/v1';
        $this->rest_base = 'offers';

        $this->username = '';
        $this->password = '';
        $this->token = 'cwZhZ1xhZk5DdFNjVwd!Zm1AdlVFcHRKDjQ~';
        $this->company_id = 443352;
        $this->domestic_offers = 30917;
        $this->foreign_offers = 31352;
    }

    public function register_routes()
    {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base, array(
                array(
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_internships'),
                ),
            ));
    }

    public function create_url($report_id)
    {
        $username = $this->username;
        $password = $this->password;
        $token = $this->token;
        $company_id = $this->company_id;
        return "https://iaeste.smartsimple.ie/API/1/report/?username=$username&password=$password&apitoken=$token&companyid=$company_id&reportid=$report_id";
    }

    public function get_internships($request)
    {
        $url = $this->create_url($this->foreign_offers);
        $request = new WP_Http;
        $result = $request->request($url);
        $json = json_decode($result['body'], true);
        return $json['records'];
    }

    public function hook_rest_server()
    {
        \add_action('rest_api_init', array($this, 'register_routes'));
    }
}

$ApiBaseController = new WP_REST_Internships_Controller();
$ApiBaseController->hook_rest_server();
