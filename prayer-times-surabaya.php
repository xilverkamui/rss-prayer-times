<?php
// Website URL containing prayer times
$link = 'https://api.aladhan.com/v1/timingsByAddress/{tanggal}?address={alamat}&method=20';
$address = 'Surabaya,Indonesia';
$date = date('d-m-Y');
$link = str_replace('{alamat}',$address,str_replace('{tanggal}',$date,$link));
$output = 'rss';
$cache = no;

// Download content from the provided URL
$html_content = file_get_contents($link);
$output = isset($_GET['output']) ? $_GET['output'] : 'rss';
$cache = isset($_GET['$cache']) ? $_GET['$cache'] : yes;

// Getting prayer times from the downloaded HTML script
$feedLink = htmlspecialchars( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8' );
$feedHome = htmlspecialchars( 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']), ENT_QUOTES, 'UTF-8' );

$prayer_times = json_decode($html_content);
$city = $address;
//$date = $prayer_times['Date'];
$urlParts = parse_url($link);
$guid = $urlParts['scheme'] . "://". $urlParts['host'] . $urlParts['path'];
$subuh = $prayer_times->data->timings->Fajr;
$dhuhur = $prayer_times->data->timings->Dhuhr;
$ashar = $prayer_times->data->timings->Asr;
$maghrib = $prayer_times->data->timings->Maghrib;
$isya = $prayer_times->data->timings->Isha;
$tanggalHijri = $prayer_times->data->date->hijri->day . " " . $prayer_times->data->date->hijri->month->en . " " . $prayer_times->data->date->hijri->year;
$pubDate = date('D, d M Y H:i:s O');
$tanggal = $prayer_times->data->date->readable;
$day = $prayer_times->data->date->gregorian->weekday->en;
$dayHijri = $prayer_times->data->date->hijri->weekday->en;

$content  = '
<strong>' . $day . ", " . $tanggal . '</strong><br>
<strong>' . $dayHijri . ", " . $tanggalHijri . '</strong><br>
Subuh &nbsp; &nbsp;: ' . $subuh . '<br>
Dhuhur &nbsp;: ' . $dhuhur . '<br>
Ashar &nbsp; &nbsp; &nbsp;: ' . $ashar . '<br>
Maghrib : ' . $maghrib . '<br>
Isya &nbsp; &nbsp; &nbsp; &nbsp; : ' . $isya . '<br><br>

Ayo sholat berjamaah !';
    
// Handle output based on the 'filetype' query parameter
if ($output === 'html') {
    header('Content-Type: text/html');
    echo "<html>";
    //print_r($prayer_times);
    echo "
    Link: " . $link . "<br>
    Feed Link: " . $feedLink . "<br>
    Feed Home: " . $feedHome . "<br>
    GUID: " . $guid . "<br>
    Published Date: " . $pubDate . "<br>
    City: " . $city . "<br>
    Date: " . $date . "<br>
    ";
    echo $content;
    echo "</html>";
} else {
    // Output as RSS
    header('Content-type: application/xml');
    if ($static) {
        header('Cache-Control: public, max-age=14400');
    }

    
    echo '<?xml version="1.0" encoding="UTF-8"?> 
<rss version="2.0" 
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:content="http://purl.org/rss/1.0/modules/content/" 
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:creativeCommons="http://backend.userland.com/creativeCommonsRssModule" 
    xmlns:media="http://search.yahoo.com/mrss/"
>
<channel>
<title>Jadwal Waktu Sholat Kota ' . $city . '</title>
<atom:link href="' . $feedLink . '" rel="self" type="application/rss+xml" />
<link>' . $feedHome . '</link>
<description>Jadwal Waktu Sholat Kota ' . $city . '</description>
<language>id-ID</language>
<copyright>Copyright ' . date('Y') . '</copyright>
<creativeCommons:license>http://creativecommons.org/licenses/by-nc-sa/3.0/</creativeCommons:license>
<item>
    <title>Jadwal Waktu Sholat Kota ' . $city . '</title>
    <guid>' . $guid . '</guid>
    <pubDate>' . $pubDate . '</pubDate>
    <dc:creator>Xilver Kamui</dc:creator>
    <description><![CDATA[' . $content . ']]></description>
    <content:encoded><![CDATA[' . $content . ']]>
    </content:encoded>
    <media:content medium="image" url=""/>
</item>

</channel>
</rss>';
}
?>
