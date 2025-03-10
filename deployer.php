<?php
// Hata ayıklama için hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Git repo bilgileri
$repo = "git@github.com:seyitErdemir/deployPHP.git"; // GitHub repo URL'si
$branch = "main"; // Kullanmak istediğin branch
$deployDir = "/home/admin/web/seyiterdemir.shop/public_html";
$tempRepoDir = "/home/admin/web/seyiterdemir.shop/temp_repo"; // Geçici klonlama dizini

// SSH anahtarı dosyasını ayarla
$sshKey = "/home/admin/.ssh/deploy_key";

// `which ssh` çıktısını al
$sshPath = trim(shell_exec("which ssh"));

echo "SSH yolu: $sshPath <br>";

// Eğer `public_html` klasörü zaten varsa, içeriği temizle ama `deployer.php` KALSIN
if (is_dir($deployDir)) {
    echo "🚨 Mevcut `public_html` klasörü bulunuyor, içi temizleniyor (deployer.php korunacak)...<br>";
    shell_exec("find $deployDir -mindepth 1 ! -name 'deployer.php' -exec rm -rf {} +");
}

// Geçici klonlama dizinini temizle
if (is_dir($tempRepoDir)) {
    shell_exec("rm -rf $tempRepoDir");
}

// Git repoyu geçici dizine klonla
echo "🚀 Git repositoyu geçici dizine klonluyoruz...<br>";
$cloneCommand = "GIT_SSH_COMMAND=\"$sshPath -i $sshKey -o StrictHostKeyChecking=no\" git clone -b $branch $repo $tempRepoDir 2>&1";
echo "Çalıştırılan komut: <pre>$cloneCommand</pre><br>";

$cloneOutput = shell_exec($cloneCommand);
echo "<pre>$cloneOutput</pre>";

// Eğer klonlama başarılıysa, dosyaları `public_html` içine taşı
if (is_dir("$tempRepoDir/.git")) {
    echo "📂 Klonlanan dosyalar taşınıyor...<br>";
    shell_exec("mv $tempRepoDir/* $deployDir/");
    shell_exec("rm -rf $tempRepoDir"); // Geçici repo dizinini sil
    echo "✅ Dosyalar başarıyla taşındı.<br>";
} else {
    die("❌ HATA: Klonlama başarısız oldu, lütfen SSH erişimini ve deploy key'i kontrol edin.");
}

echo "🎉 Deployment tamamlandı!";
?>
