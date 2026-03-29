<?php

class PasswordResetService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function resetPassword(array $user, string $new_password, string $confirm_password): bool {
        // 1. Validation check
        if ($new_password !== $confirm_password) {
            return false;
        }

        // 2. Hash the password using the modern standard (matches your AuthService)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 3. UserFinder fetches the ID as 'id', so we grab it securely
        $user_id = $user['id'] ?? 0;
        
        if (empty($user_id)) {
            return false; // Failsafe
        }

        // 4. Update the specific user via Repository
        $type = strtoupper($user['type'] ?? 'CUSTOMER');
        return $this->userRepository->updatePassword((int)$user_id, $type, $hashed_password);
    }
}