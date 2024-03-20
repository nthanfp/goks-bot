<?php
date_default_timezone_set('Asia/Jakarta');

require 'vendor/autoload.php';

use Nathan\GoksBot\GOKSModel;

$model = new GOKSModel(true);

echo "[?][" . date('d-M-Y H:i:s') . "] Enter your email: ";
$email = trim(fgets(STDIN));

echo "[?][" . date('d-M-Y H:i:s') . "] Enter your password: ";
$password = trim(fgets(STDIN));

echo "[?][" . date('d-M-Y H:i:s') . "] Refresh time (seconds): ";
$refresh = trim(fgets(STDIN));

echo "[?][" . date('d-M-Y H:i:s') . "] Telegram Chat ID: ";
$telegramId = trim(fgets(STDIN));

$login = $model->loginUser($email, $password);

if (isset($login['success'])) {
    $user = $model->getUsername();
    $model->sendTelegramMessage($telegramId, "Date: " . date('d M Y H:i:s') . "\nUser : {$user}\n\nLogin success...");
    echo "[~][" . date('d-M-Y H:i:s') . "][{$user}] Login success...\n";

    while (true) {
        // Ambil waktu saat ini
        $currentTime = time();
        $currentMinute = date('i', $currentTime);
        $currentSecond = date('s', $currentTime);

        if ($currentSecond % $refresh === 0) {
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
                        $model->sendTelegramMessage($telegramId, "Date: " . date('d M Y H:i:s') . "\n\nUser : {$user}\nRedeem success...");
                    } else {
                        $status = "Redeem Error";
                        $model->sendTelegramMessage($telegramId, "Date: " . date('d M Y H:i:s') . "\n\nUser : {$user}\nRedeem error...");
                    }
                    echo "[~][" . date('d-M-Y H:i:s') . "][{$user}] {$out_id} - {$out_name} - $status\n";
                }
            } else {
                $out_date = date('d-M-Y H:i:s');
                echo "[!][" . date('d-M-Y H:i:s') . "][{$user}] No data available.\n";
            }

            sleep(5);
        } else {
            sleep(1);
        }
    }
} else {
    echo "[!][" . date('d-M-Y H:i:s') . "] Login failed.\n";
}
