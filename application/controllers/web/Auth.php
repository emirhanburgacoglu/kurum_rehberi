<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Auth_service $auth_service
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Session $session
 */
class Auth extends Frontend_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('services/Auth_service');
    }

    public function login()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $message = $this->session->flashdata('auth_message');
            return $this->output->set_output($message ? $message : 'Login icin POST ile email ve password gonderin.');
        }

        $result = $this->auth_service->login(
            $this->input->post('email', TRUE),
            $this->input->post('password', FALSE)
        );

        if ($result['success']) {
            redirect('web/home');
            return;
        }

        $this->session->set_flashdata('auth_message', $result['message']);
        redirect('giris');
    }

    public function register()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $message = $this->session->flashdata('auth_message');
            return $this->output->set_output($message ? $message : 'Kayit icin POST ile name, email, password gonderin.');
        }

        $payload = array(
            'name' => $this->input->post('name', TRUE),
            'email' => $this->input->post('email', TRUE),
            'password' => $this->input->post('password', FALSE),
            'phone' => $this->input->post('phone', TRUE),
            'role' => $this->input->post('role', TRUE),
            'status' => $this->input->post('status', TRUE)
        );

        $result = $this->auth_service->register($payload);

        if ($result['success']) {
            redirect('web/home');
            return;
        }

        $this->session->set_flashdata('auth_message', $result['message']);
        redirect('kayit');
    }

    public function logout()
    {
        $this->auth_service->logout();
        redirect('giris');
    }

    public function forgot_password()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $message = $this->session->flashdata('auth_message');
            return $this->output->set_output($message ? $message : 'Sifre sifirlama icin POST ile email gonderin.');
        }

        $result = $this->auth_service->create_reset_token($this->input->post('email', TRUE));
        $this->session->set_flashdata('auth_message', $result['message']);
        redirect('sifremi-unuttum');
    }

    public function reset_password($token = NULL)
    {
        $token = (string) $token;
        if ($token === '') {
            show_404();
            return;
        }

        if ($this->input->method(TRUE) !== 'POST') {
            return $this->output->set_output('Yeni sifre icin POST ile password gonderin.');
        }

        $result = $this->auth_service->reset_password($token, $this->input->post('password', FALSE));
        $this->session->set_flashdata('auth_message', $result['message']);
        redirect('giris');
    }
}
