<?php

$debug = false;
date_default_timezone_set('Asia/Jakarta');
$url = "https://api.myquran.com/v1/sholat/jadwal/{kodeKota}/{tanggal}";
$kota = "Surabaya";
$kodeKota = "1638";

if (isset($_GET['kota']))   $kota = $_GET['kota'];

//Initialization
$tanggal = date("Y/m/d");
$url = str_replace("{tanggal}",$tanggal, str_replace("{kodeKota}",$kodeKota,$url));
$feedLink = htmlspecialchars( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8' );
$feedHome = htmlspecialchars( 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']), ENT_QUOTES, 'UTF-8' );
$pubDate = date('D, d M Y H:i:s O');

$config = json_decode(file_get_contents($url),false);
if ($config === null) die ('Ada kesalahan pada config');

$tanggal2 = $config -> data -> jadwal -> tanggal;
$tanggal2 = str_replace("Minggu","Ahad",$tanggal2);
$subuh = $config -> data -> jadwal -> subuh;
$dhuhur = $config -> data -> jadwal -> dzuhur;
$ashar = $config -> data -> jadwal -> ashar;
$maghrib = $config -> data -> jadwal -> maghrib;
$isya = $config -> data -> jadwal -> isya;
//$guid = str_replace(".php","",$feedLink) . "-" . str_replace("/","-",$tanggal) . ".php";
$guid = $feedLink . "?tanggal=" . str_replace("/","",$tanggal);

if ($debug || isset($_GET['tanggal'])) {
    $content = '<html>
    <head>
    <meta name="twitter:card" content="summary">
    <meta property="og:title" content="Jadwal Waktu Sholat Kota Surabaya">
    <meta property="og:description" content="Jadwal Waktu Sholat Kota Surabaya dan Sekitarnya">
    <meta property="og:url" content="waktu-sholat.php">
    </head>
    <h1 align=center>Jadwal Waktu Sholat <br> Kota ' . $kota . '</h1>
    <p align=center>' . $tanggal2 . '</p>
    <table align=center>
        <tr><td>Subuh  </td><td> : </td><td>' . $subuh . '</td></tr>
        <tr><td>Dhuhur </td><td> : </td><td>' . $dhuhur . '</td></tr>
        <tr><td>Ashar  </td><td> : </td><td>' . $ashar . '</td></tr>
        <tr><td>Maghrib </td><td> : </td><td>' . $maghrib . '</td></tr>
        <tr><td>Isya   </td><td> : </td><td>' . $isya . '</td></tr>
    </table><br>
    <p align=center>Ayo sholat berjamaah !</p>
    </html>';
    echo $content;
    
    //echo "URL: " . $url . PHP_EOL;
    //print_r ($config);
    //print_r ($config -> data -> jadwal);
}
else {
    header('Content-type: application/xml');
    //header('Cache-Control: public, max-age=0');
    
    $content  = '
    <strong>' . $tanggal2 . '</strong><br>
    Subuh &nbsp; &nbsp;: ' . $subuh . '<br>
    Dhuhur &nbsp;: ' . $dhuhur . '<br>
    Ashar &nbsp; &nbsp; &nbsp;: ' . $ashar . '<br>
    Maghrib : ' . $maghrib . '<br>
    Isya &nbsp; &nbsp; &nbsp; &nbsp; : ' . $isya . '<br><br>
    
    Ayo sholat berjamaah !';
    
    echo '<?xml version="1.0" encoding="UTF-8"?> 
<rss version="2.0" 
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:content="http://purl.org/rss/1.0/modules/content/" 
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:creativeCommons="http://backend.userland.com/creativeCommonsRssModule" 
    xmlns:media="http://search.yahoo.com/mrss/"
>

<channel>
<title>Jadwal Waktu Sholat Kota ' . $kota . '</title>
<atom:link href="' . $feedLink . '" rel="self" type="application/rss+xml" />
<link>' . $feedHome . '</link>
<description>Jadwal Waktu Sholat Kota ' . $kota . '</description>
<language>id-ID</language>
<copyright>Copyright ' . date('Y') . '</copyright>
<creativeCommons:license>http://creativecommons.org/licenses/by-nc-sa/3.0/</creativeCommons:license>
<item>
    <title>Jadwal Waktu Sholat Kota ' . $kota . '</title>
    <guid>' . $guid . '</guid>
    <pubDate>' . $pubDate . '</pubDate>
    <dc:creator>Xilver Kamui</dc:creator>
    <description><![CDATA[' . $content . ']]></description>
    <content:encoded><![CDATA[' . $content . ']]>
    </content:encoded>
</item>

</channel>
</rss>
    ';
}



?>
