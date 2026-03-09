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
        $filters = $this->normalize_public_filters($filters);

        /**
         * Hızlı arama kutusu senaryosu:
         * Sadece "q" geldiyse modelin optimize edilmiş keyword aramasını kullan.
         * Ek filtre varsa list() ile tek sorguda tüm koşulları uygula.
         */
        $hasOnlyKeyword = !empty($filters['q'])
            && empty($filters['city_id'])
            && empty($filters['district_id'])
            && empty($filters['category_id'])
            && empty($filters['sub_category_id']);

        if ($hasOnlyKeyword) {
            $data = $this->CI->Institution_model->search_by_keyword($filters['q'], 50);
        } else {
            $data = $this->CI->Institution_model->list($filters, 1000, 0);
        }

        foreach ($data as &$item) {
            $this->decorate_list_item($item);
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

    /**
     * Public (web/api) listelerde sadece aktif kurumların görünmesini garanti eder.
     * Ayrıca modelin beklediği filtre anahtarlarını normalize ederek gereksiz/verisiz
     * parametreleri sorguya taşımayı engeller.
     *
     * @param  array $filters
     * @return array
     */
    private function normalize_public_filters($filters)
    {
        $filters = is_array($filters) ? $filters : array();

        return array(
            // Public tarafta zorunlu kural: sadece aktif kurumlar.
            'status' => 1,
            'q' => isset($filters['q']) ? trim((string) $filters['q']) : '',
            'city_id' => isset($filters['city_id']) ? (string) $filters['city_id'] : '',
            'district_id' => isset($filters['district_id']) ? (string) $filters['district_id'] : '',
            'category_id' => isset($filters['category_id']) ? (string) $filters['category_id'] : '',
            'sub_category_id' => isset($filters['sub_category_id']) ? (string) $filters['sub_category_id'] : '',
            // Modeldeki varsayılan sıralama zaten priority; burada niyeti açıkça belirtiyoruz.
            'sort' => isset($filters['sort']) ? (string) $filters['sort'] : 'priority'
        );
    }

    /**
     * Liste çıktısını UI/API tüketimine hazır hale getirir.
     * - thumbnail: Görsel yoksa default görsel üretir
     * - display_name: Kart tasarımlarında taşmayı önlemek için kısaltılmış isim
     *
     * @param array $item
     * @return void
     */
    private function decorate_list_item(&$item)
    {
        $name = isset($item['name']) ? (string) $item['name'] : '';
        $item['thumbnail'] = !empty($item['image_path'])
            ? base_url($item['image_path'])
            : base_url('assets/img/default-kurum.png');

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $item['display_name'] = (mb_strlen($name, 'UTF-8') > 50)
                ? mb_substr($name, 0, 47, 'UTF-8') . "..."
                : $name;
            return;
        }

        $item['display_name'] = (strlen($name) > 50)
            ? substr($name, 0, 47) . "..."
            : $name;
    }
}
