<?php
date_default_timezone_set('Asia/Jakarta');

require 'vendor/autoload.php';

use Nathan\GoksBot\GOKSModel;

$model = new GOKSModel(true);

echo "[?][" . date('d-M-Y H:i:s') . "] Enter your email: ";
$email = trim(fgets(STDIN));

echo "[?][" . date('d-M-Y H:i:s') . "] Enter your password: ";
$password = trim(fgets(STDIN));

echo "[?][" . date('d-M-Y H:i:s') . "] Refresh time (minutes): ";
$refresh = trim(fgets(STDIN));

$login = $model->loginUser($email, $password);

if (isset($login['success'])) {
    $user = $model->getUsername();
    $model->sendTelegramMessage('358563145', "Date: " . date('d-M-Y H:i:s') . "\nUser : {$user}\nLogin success...");
    echo "[~][" . date('d-M-Y H:i:s') . "][{$user}] Login success...\n";

    while (true) {
        // Ambil waktu saat ini
        $currentTime = time();

        // Ambil menit dari waktu saat ini
        $currentMinute = date('i', $currentTime);

        // Periksa apakah menit saat ini bisa dibagi dengan 5 (atau 0)
        if ($currentMinute % 5 === 0) {
            // Jalankan blok kode di sini
            echo "[~][" . date('d-M-Y H:i:s') . "][{$user}] Get product by brand...\n";
            $result = $model->getBrandProduct(32);
            if (!empty($result['pageData'])) {
                foreach ($result['pageData'] as $item) {
                    $description = json_decode($item['description'], true);
                    $out_date = date('d-M-Y H:i:s');
                    $out_id = $item['id'];
                    $out_name = $item['name'];
                    $redeem = $model->redeemProduct($out_id);
                    if ($redeem['id']) {
                        $status = "Redeemed";
                        $model->sendTelegramMessage('358563145', "Date: " . date('d-M-Y H:i:s') . "\nUser : {$user}\nRedeem success...");
                    } else {
                        $status = "Redeem Error";
                        $model->sendTelegramMessage('358563145', "Date: " . date('d-M-Y H:i:s') . "\nUser : {$user}\nRedeem error...");
                    }
                    echo "[~][" . date('d-M-Y H:i:s') . "][{$user}] {$out_id} - {$out_name} - $status\n";
                }
            } else {
                $out_date = date('d-M-Y H:i:s');
                echo "[!][" . date('d-M-Y H:i:s') . "][{$user}] No data available.\n";
            }

            // Tunggu selama lima menit sebelum menjalankan kembali loop
            sleep(60 * $refresh); // 300 detik = 5 menit
        } else {
            // Tunggu satu menit sebelum memeriksa kembali
            sleep(5); // 60 detik = 1 menit
        }
    }
} else {
    echo "[!][" . date('d-M-Y H:i:s') . "] Login failed.\n";
}
