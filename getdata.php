<?php
// Mendapatkan jadwal untuk 1 tahun penuh dan menyimpannya dalam file JSON
// Konfigurasi default
$defaultYear = date('Y') + 1; // Tahun depan
$defaultCityId = 1638; // ID Kota Surabaya

// Ambil parameter dari query string
$year = isset($_GET['year']) ? (int)$_GET['year'] : $defaultYear;
$cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : $defaultCityId;

// URL API (untuk mendapatkan nama kota dan jadwal)
$apiBaseUrl = "https://api.myquran.com/v2/sholat/jadwal/";

// Folder output
$outputDir = "data/";
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Ambil nama kota dari API
$cityInfoUrl = "{$apiBaseUrl}{$cityId}/{$year}/01"; // Menggunakan bulan Januari sebagai acuan
$response = file_get_contents($cityInfoUrl);
if ($response === false) {
    die("Gagal mendapatkan informasi kota. Periksa ID kota atau koneksi.");
}

$cityInfo = json_decode($response, true);
if (!$cityInfo || !$cityInfo['status']) {
    die("Informasi kota tidak valid. Periksa ID kota.");
}

// Nama kota diambil dari data API
$defaultCityName = $cityInfo['data']['lokasi'];

// Array untuk menyimpan data jadwal sholat
$dataOutput = [
    "status" => true,
    "request" => [
        "path" => "/sholat/jadwal/{$cityId}/{$year}"
    ],
    "data" => [
        "id" => $cityId,
        "lokasi" => $defaultCityName,
        "daerah" => $cityInfo['data']['daerah'],
        "jadwal" => [
            $year => [] // Menyusun jadwal berdasarkan tahun
        ]
    ]
];


for ($month = 1; $month <= 12; $month++) {
    $month = str_pad($month, 2, '0', STR_PAD_LEFT); // Format bulan menjadi 2 digit
    $url = "{$apiBaseUrl}{$cityId}/{$year}/{$month}";

    // Ambil data dari API
    $response = file_get_contents($url);
    if ($response === false) {
        echo "Gagal mengambil data untuk bulan {$month}. Melanjutkan...\n";
        continue;
    }

    // Decode JSON response
    $data = json_decode($response, true);
    if (!$data || !$data['status']) {
        echo "Data tidak valid untuk bulan {$month}. Melanjutkan...\n";
        continue;
    }

    // Tambahkan data jadwal untuk bulan ini
    $dataOutput['data']['jadwal'][$year][] = getSholatJadwal($cityId, $year, $month);
    
    // Beri jeda 1 detik agar tidak terlalu banyak permintaan API
    sleep(1);
}

// Nama file output
$cityInFileName = str_replace(" ","-",$defaultCityName);
$fileName = "{$outputDir}{$year}-{$cityInFileName}.json";

// Simpan data ke file JSON
if (file_put_contents($fileName, json_encode($dataOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )) === false) {
    die("Gagal menyimpan file JSON.");
}

echo "Data jadwal sholat untuk {$year} telah disimpan di file {$fileName}.\n";
//print_r ($dataOutput);

// Fungsi untuk mendapatkan tanggal Hijriyah dari API MyQuran
function getHijriDate($gregorianDate) {
    $apiUrl = "https://api.myquran.com/v2/cal/hijr/" . $gregorianDate; // Format API untuk mendapatkan Hijriyah
    $response = file_get_contents($apiUrl);
    
    if ($response === false) {
        return null; // Jika gagal, kembalikan null
    }
    
    // Decode data JSON dari API
    $data = json_decode($response, true);
    
    // Cek apakah data Hijriyah tersedia
    if (isset($data['data']['date'])) {
        // Format tanggal Hijriyah menjadi format yang diinginkan
        return $data['data']['date'][1]; // Mengambil tanggal Hijriyah (misalnya "16 Dzulhijjah 1445 H")
    }

    return null; // Jika tidak ada data Hijriyah
}

// Fungsi untuk mendapatkan jadwal dan menambahkan tanggal Hijriyah
function getSholatJadwal($cityId, $year, $month) {
    $apiUrl = "https://api.myquran.com/v2/sholat/jadwal/{$cityId}/{$year}/{$month}";
    echo $apiUrl . "<br>" . PHP_EOL;
    $response = file_get_contents($apiUrl);
    
    if ($response === false) {
        echo "Gagal mengambil data untuk bulan {$month}. Melanjutkan...\n";
        return null; // Jika gagal, kembalikan null
    }
    
    // Decode data JSON dari API
    //print_r ($response);
    $data = json_decode($response, true);
    if (!$data || !$data['status']) {
        echo "Data tidak valid untuk bulan {$month}. Melanjutkan...\n";
        return null;
    }
    //print_r ($data['data']['jadwal']);

    // Cek apakah data jadwal tersedia
    if (isset($data['data']['jadwal'])) {
        // Loop untuk menambahkan tanggal Hijriyah ke setiap jadwal
        foreach ($data['data']['jadwal'] as &$jadwal) {
            $hijriDate = getHijriDate($jadwal['date']); // Mendapatkan tanggal Hijriyah untuk tanggal tersebut
            $jadwal['hijri'] = $hijriDate ?? "Tidak tersedia"; // Menambahkan tanggal Hijriyah ke dalam jadwal
        }
    }

    // Transformasi format tanggal di setiap jadwal
    $jadwalBulan = array_map(function ($jadwal) {
        $jadwal['tanggal'] = formatTanggal($jadwal['date']);
        return $jadwal;
    }, $data['data']['jadwal']);
    //print_r ($jadwalBulan);
    
    // Tambahkan data jadwal untuk bulan ini
    return [
        "month" => (int)$month,
        //"schedule" => $data['data']['jadwal']
        "schedule" => $jadwalBulan
    ];

}

// Fungsi untuk mengubah format tanggal
function formatTanggal($tanggal) {
    $hariIndonesia = [
        "Sunday" => "Ahad",
        "Monday" => "Senin",
        "Tuesday" => "Selasa",
        "Wednesday" => "Rabu",
        "Thursday" => "Kamis",
        "Friday" => "Jumat",
        "Saturday" => "Sabtu"
    ];

    $bulanIndonesia = [
        "January" => "Januari",
        "February" => "Februari",
        "March" => "Maret",
        "April" => "April",
        "May" => "Mei",
        "June" => "Juni",
        "July" => "Juli",
        "August" => "Agustus",
        "September" => "September",
        "October" => "Oktober",
        "November" => "November",
        "December" => "Desember"
    ];

    $dateTime = strtotime($tanggal);
    $hari = date("l", $dateTime);
    $bulan = date("F", $dateTime);

    return $hariIndonesia[$hari] . ", " . date("j", $dateTime) . " " . $bulanIndonesia[$bulan] . " " . date("Y", $dateTime);
}

?>
