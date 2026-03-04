<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Institution_service $institution_service
 * @property CI_Input $input
 */
class Institutions extends API_Controller
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

        $this->response(array(
            'success' => TRUE,
            'message' => 'Institutions loaded.',
            'data' => array(
                'filters' => $filters,
                'total' => count($institutions),
                'institutions' => $institutions
            ),
            'errors' => NULL
        ), 200);
    }
    public function show($id = null)
    {
        $result = $this->institution_service->get_detail($id);

        if (!$result['success']) {
            $status = isset($result['errors']['id']) && $result['message'] === 'Institution not found.' ? 404 : 400;
            return $this->response($result, $status);
        }

        return $this->response($result, 200);
    }
}
