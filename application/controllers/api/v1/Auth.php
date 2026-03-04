<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Auth_service $auth_service
 * @property CI_Input $input
 */
class Auth extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('services/Auth_service');
    }

    public function login()
    {
        $result = $this->auth_service->login(
            $this->input->post('email', TRUE),
            $this->input->post('password', FALSE)
        );

        return $this->response($result, $result['success'] ? 200 : 401);
    }

    public function register()
    {
        $payload = array(
            'name' => $this->input->post('name', TRUE),
            'email' => $this->input->post('email', TRUE),
            'password' => $this->input->post('password', FALSE),
            'phone' => $this->input->post('phone', TRUE),
            'role' => $this->input->post('role', TRUE),
            'status' => $this->input->post('status', TRUE)
        );

        $result = $this->auth_service->register($payload);
        return $this->response($result, $result['success'] ? 201 : 422);
    }

    public function logout()
    {
        $this->auth_service->logout();
        return $this->response(array(
            'success' => TRUE,
            'message' => 'Logout successful.',
            'data' => NULL,
            'errors' => NULL
        ), 200);
    }

    public function forgot_password()
    {
        $result = $this->auth_service->create_reset_token($this->input->post('email', TRUE));
        return $this->response($result, $result['success'] ? 200 : 422);
    }

    public function reset_password()
    {
        $result = $this->auth_service->reset_password(
            $this->input->post('token', TRUE),
            $this->input->post('password', FALSE)
        );

        return $this->response($result, $result['success'] ? 200 : 422);
    }
}
