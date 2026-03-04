<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';


    // find($id): Kullanıcıyı birincil anahtarı (ID) üzerinden getirir.
    //  Gelen veriyi (int) olarak zorlayarak SQL Injection riskini azaltır ve kayıt bulunamazsa NULL döner.
    public function find($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return NULL;
        }

        return $this->db
            ->where('id', $id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }
    //find_by_email($email): Giriş (login) işlemlerinde kullanılır.
    // E-postayı trim() ile temizleyerek boşluk hatalarını engeller.
    public function find_by_email($email)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return NULL;
        }

        return $this->db
            ->where('email', $email)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }
    //   find_by_reset_token($token): Şifre sıfırlama sürecinde,
    //    URL'den gelen benzersiz anahtarın veritabanındaki karşılığını bulur.
    public function find_by_reset_token($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return NULL;
        }

        return $this->db
            ->where('reset_token', $token)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }
    // create($data): Yeni bir kullanıcı oluşturur. 
    // İşlem başarılı olursa insert_id() ile yeni oluşan kullanıcının ID'sini döner; 
    // bu değer daha sonra session veya profil oluşturma işlemlerinde kullanılır.
    public function create($data)
    {
        if (!is_array($data) || empty($data)) {
            return FALSE;
        }

        $ok = $this->db->insert($this->table, $data);
        if (!$ok) {
            return FALSE;
        }

        return (int) $this->db->insert_id();
    }
    // update($id, $data): Belirli bir kullanıcının bilgilerini günceller. 
    // Verinin dizi (array) olup olmadığını kontrol ederek sistemin çökmesini engeller.
    public function update($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0 || !is_array($data) || empty($data)) {
            return FALSE;
        }

        return (bool) $this->db
            ->where('id', $id)
            ->update($this->table, $data);
    }

    // delete($id): Kullanıcıyı sistemden siler.
    //  Dönüş tipini (bool) yaparak işlemin başarısını kontrol etmeyi sağlar.
    public function delete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }

        return (bool) $this->db
            ->where('id', $id)
            ->delete($this->table);
    }

    // docs'taki list(...) karşılığı
    public function get_list($filters = array(), $limit = 20, $offset = 0)
    {
        $limit = (int) $limit;
        $offset = (int) $offset;

        if ($limit <= 0) {
            $limit = 20;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $this->db->from($this->table);
        $this->apply_filters($filters);

        return $this->db
            ->order_by('id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    public function count($filters = array())
    {
        $this->db->from($this->table);
        $this->apply_filters($filters);

        return (int) $this->db->count_all_results();
    }

    public function set_password($id, $hash)
    {
        $id = (int) $id;
        $hash = trim((string) $hash);

        if ($id <= 0 || $hash === '') {
            return FALSE;
        }

        return $this->update($id, array('password' => $hash));
    }

    public function set_status($id, $status)
    {
        $id = (int) $id;
        $status = (int) $status;

        if ($id <= 0) {
            return FALSE;
        }

        return $this->update($id, array('status' => $status));
    }

    public function set_role($id, $role)
    {
        $id = (int) $id;
        $role = (int) $role;

        if ($id <= 0) {
            return FALSE;
        }

        return $this->update($id, array('role' => $role));
    }

    public function set_email_verified_at($id, $datetime)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }

        $value = $datetime !== NULL ? trim((string) $datetime) : NULL;
        if ($value === '') {
            $value = NULL;
        }

        return $this->update($id, array('email_verified_at' => $value));
    }

    public function set_reset_token($id, $token, $expiresAt)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }

        $tokenValue = $token !== NULL ? trim((string) $token) : NULL;
        $expiresValue = $expiresAt !== NULL ? trim((string) $expiresAt) : NULL;

        if ($tokenValue === '') {
            $tokenValue = NULL;
        }

        if ($expiresValue === '') {
            $expiresValue = NULL;
        }

        return $this->update($id, array(
            'reset_token' => $tokenValue,
            'reset_expires' => $expiresValue
        ));
    }

    public function set_remember_token($id, $token)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }

        $value = $token !== NULL ? trim((string) $token) : NULL;
        if ($value === '') {
            $value = NULL;
        }

        return $this->update($id, array('remember_token' => $value));
    }

    protected function apply_filters($filters = array())
    {
        if (!is_array($filters)) {
            return;
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== '' && $filters['status'] !== NULL) {
            $this->db->where('status', (int) $filters['status']);
        }

        if (array_key_exists('role', $filters) && $filters['role'] !== '' && $filters['role'] !== NULL) {
            $this->db->where('role', (int) $filters['role']);
        }

        if (!empty($filters['email'])) {
            $this->db->like('email', trim((string) $filters['email']));
        }

        if (!empty($filters['name'])) {
            $this->db->like('name', trim((string) $filters['name']));
        }

        if (!empty($filters['phone'])) {
            $this->db->like('phone', trim((string) $filters['phone']));
        }

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()
                ->like('name', $q)
                ->or_like('email', $q)
                ->or_like('phone', $q)
                ->group_end();
        }
    }
}
