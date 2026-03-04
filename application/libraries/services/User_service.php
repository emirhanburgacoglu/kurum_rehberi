<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_service
{
    protected $CI;
    protected $userModel;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('User_model');
        $this->userModel = new User_model();
    }

    /**
     * Yeni kullanıcı oluşturur.
     * - Zorunlu alan kontrolü
     * - Email formatı
     * - Unique email kontrolü
     * - Şifre hashleme
     * Başarılıysa user id döner, başarısızsa FALSE.
     */
    public function create_user($data)
    {
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $password = isset($data['password']) ? (string) $data['password'] : '';

        if ($name === '' || $email === '' || $password === '') {
            return FALSE;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }

        $exists = $this->userModel->find_by_email($email);
        if ($exists) {
            return FALSE;
        }

        $insert = array(
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => isset($data['phone']) ? trim((string) $data['phone']) : NULL,
            'role' => isset($data['role']) ? (int) $data['role'] : 0,
            'status' => isset($data['status']) ? (int) $data['status'] : 1
        );

        return $this->userModel->create($insert);
    }

    /**
     * ID ile kullanıcı getirir.
     */
    public function find($id)
    {
        return $this->userModel->find($id);
    }

    /**
     * Email ile kullanıcı getirir.
     */
    public function find_by_email($email)
    {
        return $this->userModel->find_by_email($email);
    }

    /**
     * Login sırasında düz şifre ile hash'i doğrular.
     */
    public function verify_password($plainPassword, $passwordHash)
    {
        $plainPassword = (string) $plainPassword;
        $passwordHash = (string) $passwordHash;

        if ($plainPassword === '' || $passwordHash === '') {
            return FALSE;
        }

        return password_verify($plainPassword, $passwordHash);
    }

    /**
     * Kullanıcı günceller.
     * Şifre gelirse hashleyip yazar.
     */
    public function update_user($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0 || !is_array($data) || empty($data)) {
            return FALSE;
        }

        $update = array();

        if (array_key_exists('name', $data)) {
            $update['name'] = trim((string) $data['name']);
        }

        if (array_key_exists('email', $data)) {
            $email = trim((string) $data['email']);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return FALSE;
            }

            $current = $this->userModel->find($id);
            if (!$current) {
                return FALSE;
            }

            $exists = $this->userModel->find_by_email($email);
            if ($exists && (int) $exists['id'] !== $id) {
                return FALSE;
            }

            $update['email'] = $email;
        }

        if (array_key_exists('phone', $data)) {
            $phone = trim((string) $data['phone']);
            $update['phone'] = ($phone === '') ? NULL : $phone;
        }

        if (array_key_exists('role', $data)) {
            $update['role'] = (int) $data['role'];
        }

        if (array_key_exists('status', $data)) {
            $update['status'] = (int) $data['status'];
        }

        if (array_key_exists('password', $data) && (string) $data['password'] !== '') {
            $update['password'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        if (empty($update)) {
            return FALSE;
        }

        return $this->userModel->update($id, $update);
    }

    /**
     * Şifreyi direkt değiştirir (hashleyerek).
     */
    public function set_password($id, $plainPassword)
    {
        $id = (int) $id;
        $plainPassword = (string) $plainPassword;

        if ($id <= 0 || $plainPassword === '') {
            return FALSE;
        }

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        return $this->userModel->set_password($id, $hash);
    }

    /**
     * reset_token + reset_expires alanlarını set/clear eder.
     */
    public function set_reset_token($id, $token, $expiresAt)
    {
        return $this->userModel->set_reset_token($id, $token, $expiresAt);
    }

    /**
     * remember_token alanını set/clear eder.
     */
    public function set_remember_token($id, $token)
    {
        return $this->userModel->set_remember_token($id, $token);
    }
}
