<?php

require_once "./config/Connection.php";
require_once "./../vendor/autoload.php";
require_once "./mainmodule/Auth.php";

use Firebase\JWT\JWT;

    class Auth{
        protected $pdo;
        protected $gm;
        private $conn;
        private $key;


        public function __construct(\PDO $pdo)
        {   

            $databaseService = new Connection();
            $this->conn = $databaseService->connect();

            $this->pdo = $pdo;
            $this->gm = new GlobalMethods($pdo);
            $this->key = $this->gm->generateSecretKey();
            
        }


        // password checking

        private function check_password($password, $existing_hash){
            $hash = crypt($password, $existing_hash);
            if($hash === $existing_hash){
                return true;
            }
            return false;
        }



        // password encryption
        
        private function encrypt_password($password_string){
            $hash_format = "$2y$10$";
            $salt_length = 22;
            $salt = $this->generate_salt($salt_length);
            return crypt($password_string, $hash_format . $salt);
        }


        // generation key/salt

        private function generate_salt($length){
            $urs = md5(uniqid(mt_rand(), true));
            $b64_string = base64_encode($urs);
            $mb64_string = str_replace('+', '.', $b64_string);
            return substr($mb64_string, 0, $length);
        }


        // generate token

        private function generate_token($id){
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            $payload = json_encode(['user_id' => $id]);
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'allergicakosacommitment', true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
            return $jwt;
        }


        public function add_user($received_data){
            $received_data->password = $this->encrypt_password($received_data->password);
            $res = $this->gm->insert("users_tbl", $received_data); 
            if($res['code']==200){
                return $this->gm->returnPayload(null, 'success', 'successfully inserted data', $res['code']);
            }
            return $this->gm->returnPayload(null, 'failed', 'failed to insert data', $res['code']);
        }

        public function login($email, $password) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user || !password_verify($password, $user['password'])) {
                return [
                    'status' => 401,
                    'message' => 'Invalid email or password'
                ];
            }

            $role = $user['role'] !== null ? $user['role'] : null;

            $payload = [
                'iss' => 'localhost',
                'aud' => 'localhost',
                'exp' => time() + 3600,
                'data' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $role
                ],
            ];
    
            $jwt = JWT::encode($payload, $this->key, 'HS256');
    
            return [
                'status' => 200,
                'jwt' => $jwt,  
                'message' => 'Login Successful',
                'role' => $role
            ];
        }
    

    }

?>