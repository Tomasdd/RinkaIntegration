<?php
$url = preg_replace('/[\r\n]+/is', '', $url);
$isHeadersSent = headers_sent();
if (!$isHeadersSent) {
    header("Location: $url", true);
    exit();
} else {
    echo '<script type="text/javascript">window.location = "' . addslashes($url) . '";</script>';
    $url = htmlentities($url, ENT_QUOTES, 'UTF-8');
    printf('Tuoj būsite nukreipti į <a href="%s">%s</a>. Prašome palaukti.', $url, $url);
}
?>