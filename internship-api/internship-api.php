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

include ('response_formatting.php');

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WP_REST_Internships_Controller extends \WP_REST_Controller
{
    protected $namespace;
    protected $rest_base;
    protected $username;
    protected $password;
    protected $token;
    protected $company_id;
    protected $domestic_offers;
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
            '/' . $this->rest_base . '/domestic', array(
                array(
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_domestic_offers'),
                ),
            ));
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/foreign', array(
                array(
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_foreign_offers'),
                ),
            ));
    }

    public function get_domestic_offers($request)
    {
        return $this->get_internships($this->domestic_offers);
    }

    public function get_foreign_offers($request)
    {
        return $this->get_internships($this->foreign_offers);
    }

    public function get_internships($offer_type)
    {
        if (!$this->is_cached($offer_type)) {
            $internships = $this->fetch_internships($offer_type);
            $this->write_to_file($internships, $offer_type);
        }
        return $this->get_cached_data($offer_type);
    }

    public function fetch_internships($offer_type)
    {
        $url = $this->create_url($offer_type);
        $request = new WP_Http;
        $result = $request->request($url);
        $json = json_decode($result['body'], true);
        return format_response($json['records']);
    }

    public function create_url($report_id)
    {
        $username = rawurlencode($this->username);
        $password = $this->password;
        $token = $this->token;
        $company_id = $this->company_id;
        return "https://iaeste.smartsimple.ie/API/1/report/?username=$username&password=$password&apitoken=$token&companyid=$company_id&reportid=$report_id";
    }

    public function get_filename($offer_type)
    {
        $path = plugin_dir_path(__FILE__);
        $file_type = ($offer_type == $this->domestic_offers) ? 'domestic' : 'foreign';
        return $path . '/' . $file_type . '.json';
    }

    public function write_to_file($data, $offer_type)
    {
        $file = $this->get_filename($offer_type);
        $open = fopen($file, 'w');
        $write = fputs($open, json_encode($data));
        fclose($open);
    }

    public function is_cached($offer_type)
    {
        $filename = $this->get_filename($offer_type);
        if (file_exists($filename)) {
            $now = new DateTime("now", new DateTimeZone('UTC'));
            $last_edit = new DateTime(date("F d Y H:i:s.", filemtime($filename)), new DateTimeZone('UTC'));
            $interval = $last_edit->diff($now);
            $hour_difference = ($interval->days * 24) + $interval->h;
            if ($hour_difference < 2) {
                return true;
            }
        }
        return false;
    }

    public function get_cached_data($offer_type)
    {
        $file = $this->get_filename($offer_type);
        return json_decode(file_get_contents($file), true);
    }

    public function hook_rest_server()
    {
        \add_action('rest_api_init', array($this, 'register_routes'));
    }
}

$ApiBaseController = new WP_REST_Internships_Controller();
$ApiBaseController->hook_rest_server();
