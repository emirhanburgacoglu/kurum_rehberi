<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_service
{
    protected $CI;
    protected $userService;
    protected $userModel;
    protected $rememberCookieName = 'remember_token';
    protected $rememberCookieExpire = 2592000; // 30 days

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('services/User_service');
        $this->CI->load->model('User_model');

        $this->userService = new User_service();
        $this->userModel = new User_model();
    }

    public function register($payload)
    {
        if (!is_array($payload)) {
            return $this->error('Invalid payload.', array('payload' => 'Payload must be an array.'));
        }

        $email = isset($payload['email']) ? trim((string) $payload['email']) : '';
        $password = isset($payload['password']) ? (string) $payload['password'] : '';
        $name = isset($payload['name']) ? trim((string) $payload['name']) : '';

        if ($name === '' || $email === '' || $password === '') {
            return $this->error('Validation failed.', array(
                'name' => 'Name is required.',
                'email' => 'Email is required.',
                'password' => 'Password is required.'
            ));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Validation failed.', array('email' => 'Email format is invalid.'));
        }

        if (strlen($password) < 6) {
            return $this->error('Validation failed.', array('password' => 'Password must be at least 6 characters.'));
        }

        $id = $this->userService->create($payload);
        if (!$id) {
            return $this->error('User could not be created.', array('email' => 'Email already exists or data is invalid.'));
        }

        $this->set_logged_in_session((int) $id);
        $user = $this->userService->find((int) $id);

        return $this->ok('Registration successful.', array('user' => $this->sanitize_user($user)));
    }

    public function login($email, $password)
    {
        $email = trim((string) $email);
        $password = (string) $password;

        if ($email === '' || $password === '') {
            return $this->error('Validation failed.', array('credentials' => 'Email and password are required.'));
        }

        $user = $this->userService->find_by_email($email);
        if (!$user) {
            return $this->error('Invalid credentials.', array('email' => 'User not found.'));
        }

        if (isset($user['status']) && (int) $user['status'] !== 1) {
            return $this->error('Account is inactive.', array('status' => 'User status is not active.'));
        }

        if (!$this->userService->verify_password($password, isset($user['password']) ? $user['password'] : '')) {
            return $this->error('Invalid credentials.', array('password' => 'Password is incorrect.'));
        }

        $this->set_logged_in_session((int) $user['id']);
        $this->userService->set_email_verified_at((int) $user['id'], date('Y-m-d H:i:s'));

        return $this->ok('Login successful.', array('user' => $this->sanitize_user($this->userService->find((int) $user['id']))));
    }

    public function attempt($credentials)
    {
        if (!is_array($credentials)) {
            return $this->error('Validation failed.', array('credentials' => 'Credentials must be an array.'));
        }

        return $this->login(
            isset($credentials['email']) ? $credentials['email'] : '',
            isset($credentials['password']) ? $credentials['password'] : ''
        );
    }

    public function logout()
    {
        $userId = $this->current_user_id();
        if ($userId !== NULL) {
            $this->clear_remember_me($userId);
        }

        $this->CI->session->unset_userdata(array(
            'logged_in',
            'user_id',
            'user_email',
            'user_name',
            'user_role'
        ));
    }

    public function is_logged_in()
    {
        return (bool) $this->CI->session->userdata('logged_in');
    }

    public function current_user_id()
    {
        if (!$this->is_logged_in()) {
            return NULL;
        }

        $id = (int) $this->CI->session->userdata('user_id');
        return $id > 0 ? $id : NULL;
    }

    public function current_user()
    {
        $id = $this->current_user_id();
        if ($id === NULL) {
            return NULL;
        }

        $user = $this->userService->find($id);
        return $user ? $this->sanitize_user($user) : NULL;
    }

    public function require_auth()
    {
        if (!$this->is_logged_in()) {
            show_error('Unauthorized', 401);
        }
    }

    public function guest_only()
    {
        if ($this->is_logged_in()) {
            redirect('web/home');
        }
    }

    public function refresh_session_user()
    {
        $userId = $this->current_user_id();
        if ($userId === NULL) {
            return;
        }

        $user = $this->userService->find($userId);
        if (!$user) {
            $this->logout();
            return;
        }

        $this->CI->session->set_userdata(array(
            'logged_in' => TRUE,
            'user_id' => (int) $user['id'],
            'user_email' => isset($user['email']) ? (string) $user['email'] : '',
            'user_name' => isset($user['name']) ? (string) $user['name'] : '',
            'user_role' => isset($user['role']) ? (int) $user['role'] : 0
        ));
    }

    public function set_remember_me($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return '';
        }

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            return '';
        }

        $ok = $this->userService->set_remember_token($userId, $token);
        if (!$ok) {
            return '';
        }

        $this->CI->input->set_cookie(array(
            'name' => $this->rememberCookieName,
            'value' => $token,
            'expire' => $this->rememberCookieExpire,
            'secure' => FALSE,
            'httponly' => TRUE
        ));

        return $token;
    }

    public function login_with_remember_token($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return $this->error('Token is required.', array('token' => 'Remember token is empty.'));
        }

        $user = $this->userModel->find_by_remember_token($token);
        if (!$user) {
            return $this->error('Invalid token.', array('token' => 'Remember token not found.'));
        }

        if (isset($user['status']) && (int) $user['status'] !== 1) {
            return $this->error('Account is inactive.', array('status' => 'User status is not active.'));
        }

        $this->set_logged_in_session((int) $user['id']);
        return $this->ok('Login successful.', array('user' => $this->sanitize_user($user)));
    }

    public function clear_remember_me($userId)
    {
        $userId = (int) $userId;
        if ($userId > 0) {
            $this->userService->set_remember_token($userId, NULL);
        }

        $this->CI->input->set_cookie(array(
            'name' => $this->rememberCookieName,
            'value' => '',
            'expire' => -3600
        ));
    }

    public function create_reset_token($email)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return $this->error('Email is required.', array('email' => 'Email is empty.'));
        }

        $user = $this->userService->find_by_email($email);
        if (!$user) {
            return $this->error('User not found.', array('email' => 'No user with this email.'));
        }

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            return $this->error('Token could not be generated.', array('token' => 'Random generator failed.'));
        }

        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $ok = $this->userService->set_reset_token((int) $user['id'], $token, $expiresAt);
        if (!$ok) {
            return $this->error('Reset token could not be saved.', array('token' => 'Database update failed.'));
        }

        return $this->ok('Reset token created.', array(
            'user_id' => (int) $user['id'],
            'token' => $token,
            'expires_at' => $expiresAt
        ));
    }

    public function reset_password($token, $newPassword)
    {
        $token = trim((string) $token);
        $newPassword = (string) $newPassword;

        if ($token === '' || $newPassword === '') {
            return $this->error('Validation failed.', array('token' => 'Token and new password are required.'));
        }

        if (strlen($newPassword) < 6) {
            return $this->error('Validation failed.', array('password' => 'Password must be at least 6 characters.'));
        }

        $user = $this->userService->find_by_reset_token($token);
        if (!$user) {
            return $this->error('Invalid token.', array('token' => 'Reset token not found.'));
        }

        $expiresAt = isset($user['reset_expires']) ? (string) $user['reset_expires'] : '';
        if ($expiresAt === '' || strtotime($expiresAt) < time()) {
            return $this->error('Token expired.', array('token' => 'Reset token has expired.'));
        }

        $ok = $this->userService->set_password((int) $user['id'], $newPassword);
        if (!$ok) {
            return $this->error('Password could not be updated.', array('password' => 'Password update failed.'));
        }

        $this->userService->set_reset_token((int) $user['id'], NULL, NULL);
        return $this->ok('Password updated successfully.', array('user_id' => (int) $user['id']));
    }

    protected function set_logged_in_session($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return;
        }

        $user = $this->userService->find($userId);
        if (!$user) {
            return;
        }

        $this->CI->session->set_userdata(array(
            'logged_in' => TRUE,
            'user_id' => (int) $user['id'],
            'user_email' => isset($user['email']) ? (string) $user['email'] : '',
            'user_name' => isset($user['name']) ? (string) $user['name'] : '',
            'user_role' => isset($user['role']) ? (int) $user['role'] : 0
        ));
    }

    protected function sanitize_user($user)
    {
        if (!is_array($user)) {
            return NULL;
        }

        unset($user['password'], $user['remember_token'], $user['reset_token']);
        return $user;
    }

    protected function ok($message, $data = NULL)
    {
        return array(
            'success' => TRUE,
            'message' => (string) $message,
            'data' => $data,
            'errors' => NULL
        );
    }

    protected function error($message, $errors = NULL)
    {
        return array(
            'success' => FALSE,
            'message' => (string) $message,
            'data' => NULL,
            'errors' => $errors
        );
    }
}
