<?php

require_once "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Inisiasi ENV Data
$db_host = $_ENV['DB_HOST'];
$db_name = $_ENV['DB_NAME'];
$db_user = $_ENV['DB_USER'];
$db_pass = $_ENV['DB_PASSWORD'];
$admin_url = $_ENV['ADMIN_URL'];
$logout_url = $_ENV['LOGOUT_URL'];

session_start();

//Udah lihat landing page belum
if(!isset($_SESSION['first'])){
    header('location:first.php');
}

//Koneksi
$conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);

//Set timezone
date_default_timezone_set("Asia/Bangkok");
$timenow = date("j-F-Y-h:i:s A");

//Check IP Address and host
$ip = $_SERVER['HTTP_CLIENT_IP'] 
    ?? $_SERVER["HTTP_CF_CONNECTING_IP"] # when behind cloudflare
    ?? $_SERVER['HTTP_X_FORWARDED'] 
    ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
    ?? $_SERVER['HTTP_FORWARDED'] 
    ?? $_SERVER['HTTP_FORWARDED_FOR'] 
    ?? $_SERVER['REMOTE_ADDR'] 
    ?? '0.0.0.0';

//Encryption function
function encryptthis($data,$key){
    $encryption_key = base64_encode($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc','$encryption_key',0,$iv);
    return base64_encode($encrypted.'::'.$iv);
};

//Decryption function
function decryptthis($data,$key){
    $encryption_key = base64_decode($key);
    list($encrypted_data, $iv) = array_pad(explode('::',base64_decode($data),2),2,null);
    return openssl_decrypt($encrypted_data,'aes-256-cbc','$encryption_key',0,$iv);
}

//Key
$key = 'aws-kelompok-monyet';


if(isset($_POST['kirim'])){
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $telp = mysqli_real_escape_string($conn,$_POST['telp']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $perusahaan = mysqli_real_escape_string($conn,$_POST['perusahaan']);
    $kepentingan = mysqli_real_escape_string($conn,$_POST['kepentingan']);

    //Encrypt dulu
    $nama2 = encryptthis($nama,$key);
    $telp2 = encryptthis($telp,$key);
    $email2 = encryptthis($email,$key);
    $perusahaan2 = encryptthis($perusahaan,$key);
    $kepentingan2 = encryptthis($kepentingan,$key);

    $query = mysqli_query($conn,"INSERT INTO guest (nama,telp,email,perusahaan,kepentingan,ip) values ('$nama2','$telp2','$email2','$perusahaan2','$kepentingan2','$ip')");

    if($query){
        //Alert and redirect
        echo "
        <script>alert('Berhasil terkirim');
        window.location.href='index.php';
        </script>";
    } else {
        //Alert and redirect
        unset($_POST['kirim']);
        echo "
        <script>alert('Gagal terkirim');
        window.history.replaceState( null, null, window.location.href )
        </script>";
    }
}

?>