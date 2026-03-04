<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Institution_service $institution_service
 * @property CI_Input $input
 */
class Home extends Frontend_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('services/Institution_service');
    }

    public function index()
    {
        $filters = array(
            'q' => $this->input->get('q', TRUE),
            'city_id' => $this->input->get('city_id', TRUE),
            'sub_category_id' => $this->input->get('sub_category_id', TRUE)
        );

        $institutions = $this->institution_service->get_filtered_list($filters);

        $data = array(
            'current' => 'home',
            'title' => 'Kurum Rehberi',
            'filters' => $filters,
            'total' => count($institutions),
            'institutions' => array_slice($institutions, 0, 10)
        );

        $this->load->view('pages/home', $data);
    }
}
