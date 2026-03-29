<?php
class RegistrationService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function register(array $data): array {
        $data['first_name']   = trim($data['first_name']);
        $data['last_name']    = trim($data['last_name']);
        $data['username']     = trim($data['username']);
        $data['email']        = trim($data['email']);
        $data['phone_number'] = !empty($data['phone_number']) ? preg_replace('/\D/', '', $data['phone_number']) : null;
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $user_id = $this->userRepository->createCustomer($data, $hashed_password);

        if ($user_id) {
            // Auto-login
            //$_SESSION['user_id']     = $user_id;
            //$_SESSION['first_name']  = $data['first_name'];
            //$_SESSION['role']        = 'CUSTOMER';

            return ['success' => true, 'user_id' => $user_id, 'message' => 'Registration successful!'];
        } 
            
        return ['success' => false, 'message' => 'Database error.Please try again ' ];
    }
}