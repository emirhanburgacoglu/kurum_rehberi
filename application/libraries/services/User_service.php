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

    public function create($data)
    {
        if (!is_array($data)) {
            return FALSE;
        }

        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $password = isset($data['password']) ? (string) $data['password'] : '';

        if ($name === '' || $email === '' || $password === '') {
            return FALSE;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }

        if ($this->exists_by_email($email)) {
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

    // Backward compatibility for existing calls.
    public function create_user($data)
    {
        return $this->create($data);
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0 || !is_array($data) || empty($data)) {
            return FALSE;
        }

        $update = array();

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                return FALSE;
            }
            $update['name'] = $name;
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

            if ($this->exists_by_email($email, $id)) {
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

        if (array_key_exists('email_verified_at', $data)) {
            $value = $data['email_verified_at'];
            $value = $value !== NULL ? trim((string) $value) : NULL;
            if ($value === '') {
                $value = NULL;
            }
            $update['email_verified_at'] = $value;
        }

        if (empty($update)) {
            return FALSE;
        }

        return $this->userModel->update($id, $update);
    }

    // Backward compatibility for existing calls.
    public function update_user($id, $data)
    {
        return $this->update($id, $data);
    }

    public function delete($id)
    {
        return $this->userModel->delete($id);
    }

    public function find($id)
    {
        return $this->userModel->find($id);
    }

    public function find_by_email($email)
    {
        return $this->userModel->find_by_email($email);
    }

    public function find_by_reset_token($token)
    {
        return $this->userModel->find_by_reset_token($token);
    }

    public function exists_by_email($email, $excludeId = NULL)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return FALSE;
        }

        $row = $this->userModel->find_by_email($email);
        if (!$row) {
            return FALSE;
        }

        if ($excludeId !== NULL && isset($row['id']) && (int) $row['id'] === (int) $excludeId) {
            return FALSE;
        }

        return TRUE;
    }

    public function list($filters = array(), $limit = 20, $offset = 0)
    {
        return $this->userModel->list($filters, $limit, $offset);
    }

    public function count($filters = array())
    {
        return (int) $this->userModel->count($filters);
    }

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

    public function verify_password($plainPassword, $passwordHash)
    {
        $plainPassword = (string) $plainPassword;
        $passwordHash = (string) $passwordHash;

        if ($plainPassword === '' || $passwordHash === '') {
            return FALSE;
        }

        return password_verify($plainPassword, $passwordHash);
    }

    public function set_status($id, $status)
    {
        return $this->userModel->set_status($id, $status);
    }

    public function set_role($id, $role)
    {
        return $this->userModel->set_role($id, $role);
    }

    public function set_email_verified_at($id, $datetime)
    {
        return $this->userModel->set_email_verified_at($id, $datetime);
    }

    public function set_reset_token($id, $token, $expiresAt)
    {
        return $this->userModel->set_reset_token($id, $token, $expiresAt);
    }

    public function set_remember_token($id, $token)
    {
        return $this->userModel->set_remember_token($id, $token);
    }
}
