<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @property CI_Controller $CI
 * @property Institution_model $Institution_model
 */
class Institution_service
{
    /** @var mixed CI super object (CI3 magic properties) */
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Institution_model');
    }

    public function get_filtered_list($filters = array())
    {
        $data = $this->CI->Institution_model->search($filters);

        foreach ($data as &$item) {
            $name = isset($item['name']) ? (string) $item['name'] : '';
            $item['thumbnail'] = !empty($item['image_path'])
                ? base_url($item['image_path'])
                : base_url('assets/img/default-kurum.png');

            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                $item['display_name'] = (mb_strlen($name, 'UTF-8') > 50)
                    ? mb_substr($name, 0, 47, 'UTF-8') . "..."
                    : $name;
            } else {
                $item['display_name'] = (strlen($name) > 50)
                    ? substr($name, 0, 47) . "..."
                    : $name;
            }
        }
        unset($item);

        return $data;
    }

    public function prepare_for_insert($post_data)
    {
        $name = isset($post_data['name']) ? trim((string) $post_data['name']) : '';
        $city_id = isset($post_data['city_id']) ? (int) $post_data['city_id'] : 0;

        return array(
            'name' => $name,
            'slug' => url_title($name, 'dash', TRUE),
            'city_id' => $city_id,
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );
    }

    public function get_detail($id)
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            return array(
                'success' => FALSE,
                'message' => 'Invalid institution id.',
                'data' => NULL,
                'errors' => array('id' => 'ID must be a positive number.')
            );
        }

        $item = $this->CI->Institution_model->find_active_by_id((int) $id);

        if (!$item) {
            return array(
                'success' => FALSE,
                'message' => 'Institution not found.',
                'data' => NULL,
                'errors' => array('id' => 'No active institution for given id.')
            );
        }

        $name = isset($item['name']) ? (string) $item['name'] : '';
        $item['thumbnail'] = !empty($item['image_path'])
            ? base_url($item['image_path'])
            : base_url('assets/img/default-kurum.png');
        $item['display_name'] = $name;

        return array(
            'success' => TRUE,
            'message' => 'Institution loaded.',
            'data' => array('institution' => $item),
            'errors' => NULL
        );
    }
}
