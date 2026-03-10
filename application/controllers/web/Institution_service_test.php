<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Institution_service test controller (manual testing page)
 *
 * URL:
 *   /index.php/web/institution_service_test
 */
class Institution_service_test extends Frontend_Controller
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
            'district_id' => $this->input->get('district_id', TRUE),
            'category_id' => $this->input->get('category_id', TRUE),
            'sub_category_id' => $this->input->get('sub_category_id', TRUE),
            'sort' => $this->input->get('sort', TRUE),
        );

        $perPage = (int) $this->input->get('per_page', TRUE);
        $page = (int) $this->input->get('page', TRUE);
        $detailId = $this->input->get('detail_id', TRUE);
        $premiumLimit = (int) $this->input->get('premium_limit', TRUE);

        $perPage = $perPage > 0 ? $perPage : 10;
        $page = $page > 0 ? $page : 1;
        $premiumLimit = $premiumLimit > 0 ? $premiumLimit : 6;

        $activeCount = $this->institution_service->get_active_count();
        $filteredList = $this->institution_service->get_filtered_list($filters);
        $paginated = $this->institution_service->get_paginated_list($filters, $perPage, $page);
        $premium = $this->institution_service->get_premium_institutions($premiumLimit);

        $detail = NULL;
        if ($detailId !== NULL && $detailId !== '') {
            $detail = $this->institution_service->get_detail($detailId);
        }

        $data = array(
            'title' => 'Institution Service Test',
            'filters' => $filters,
            'active_count' => $activeCount,
            'filtered_list' => $filteredList,
            'filtered_total' => count($filteredList),
            'paginated' => $paginated,
            'premium' => $premium,
            'detail' => $detail,
            'per_page' => $perPage,
            'page' => $page,
            'detail_id' => $detailId,
            'premium_limit' => $premiumLimit,
        );

        $this->load->view('pages/institution_service_test', $data);
    }
}
