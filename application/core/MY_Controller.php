<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Output $output
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 */
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Karakter seti ayarı (Rehber: 3.3)
        $this->output->set_header('Content-Type: text/html; charset=UTF-8');
    }
}

/**
 * Herkese Açık Web Sayfaları Tabanı
 */
class Frontend_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}

/**
 * Giriş Zorunlu Sayfalar Tabanı (Panel vb.)
 */
class Auth_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');

        // Giriş kontrolü - Rota uyumu için 'giris' kullanıldı
        if (!$this->session->userdata('logged_in')) {
            redirect('giris');
        }
    }
}

/**
 * API Endpoint'leri Tabanı
 */
class API_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // API Cevaplarını JSON formatına ayarla (Rehber: 9)
        $this->output->set_content_type('application/json', 'utf-8');
    }

    /**
     * Ortak JSON cevap fonksiyonu
     * Türkçe karakter sorunu için JSON_UNESCAPED_UNICODE eklendi
     */
    protected function response($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
