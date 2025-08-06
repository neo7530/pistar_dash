<?php
// Load the language support
require_once('../config/language.php');
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('../config/version.php');
$ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
$service = "tnygps"; // Name des systemd-Dienstes

function service_status($service) {
    $status = shell_exec("systemctl is-active $service");
    $enabled = shell_exec("systemctl is-enabled $service");
    return ['active' => trim($status), 'enabled' => trim($enabled)];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "start": shell_exec("sudo systemctl start $service"); break;
            case "stop": shell_exec("sudo systemctl stop $service"); break;
            case "enable": shell_exec("sudo systemctl enable $service"); break;
            case "disable": shell_exec("sudo systemctl disable $service"); break;
            case "save":
                if (isset($_POST["config"])) {
                    file_put_contents($conf_file, $_POST["config"]);
                }
                break;
        }
    }
}

$status = service_status($service);



?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>

    <style>
        h2,
        p,
        #live-gps,
        #live-fix,
        #live-speed {
                color: white;
        }
        button { margin: 5px; }
    </style>



    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Expert Editor" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - Digital Voice Dashboard - Expert Editor</title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">
  <?php
if(isset($_POST['data'])) {
        // File Wrangling
        exec('sudo cp /usr/local/etc/tnygps.conf /tmp/yAw432GHs5.tmp');
        exec('sudo chown www-data:www-data /tmp/yAw432GHs5.tmp');
        exec('sudo chmod 664 /tmp/yAw432GHs5.tmp');

        // Open the file and write the data
        $filepath = '/tmp/yAw432GHs5.tmp';
        $fh = fopen($filepath, 'w');
        fwrite($fh, $_POST['data']);
        fclose($fh);
        exec('sudo mount -o remount,rw /');
        exec('sudo cp /tmp/yAw432GHs5.tmp /usr/local/etc/tnygps.conf');
        exec('sudo chmod 644 /usr/local/etc/tnygps.conf');
        exec('sudo chown root:root /usr/local/etc/tnygps.conf');
        exec('sudo mount -o remount,ro /');

        // Re-open the file and read it
        $fh = fopen($filepath, 'r');
        $theData = fread($fh, filesize($filepath));

} else {
        // File Wrangling
        exec('sudo cp /usr/local/etc/tnygps.conf /tmp/yAw432GHs5.tmp');
        exec('sudo chown www-data:www-data /tmp/yAw432GHs5.tmp');
        exec('sudo chmod 664 /tmp/yAw432GHs5.tmp');

        // Open the file and read it
        $filepath = '/tmp/yAw432GHs5.tmp';
        $fh = fopen($filepath, 'r');
        $theData = fread($fh, filesize($filepath));
}
fclose($fh);

?>
<form name="test" method="post" action="">
<textarea name="data" cols="80" rows="45"><?php echo $theData; ?></textarea><br />
<input type="submit" name="submit" value="<?php echo $lang['apply']; ?>" />
</form>

</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
    <h2>tnygps-Dienststatus</h2>
    <p><strong>Aktiv:</strong> <?= $status['active'] ?> <br>
       <strong>Aktiviert:</strong> <?= $status['enabled'] ?></p>

    <form method="post">
        <button name="action" value="start">Start</button>
        <button name="action" value="stop">Stop</button>
        <button name="action" value="enable">Aktivieren</button>
        <button name="action" value="disable">Deaktivieren</button>
    </form>

    <hr>
    <h2>Live-GPS-Daten</h2>
    <div id="live-gps">Warten auf Daten...</div>
    <div id="live-fix">Fix-Status: unbekannt</div>

    <hr>


<script>
function ladeLiveDaten() {
    //fetch('http://localhost:8081/status')
    fetch(`http://<?php echo $ip ?>:8081/status`)
        .then(res => res.json())
        .then(data => {
            // Beispielhafte Felder: data.gps und data.fix
                document.getElementById('live-gps').textContent = 'GPS: ' + data.lat.toFixed(6) + ', ' + data.lon.toFixed(6);
                document.getElementById('live-fix').textContent = 'Fix: ' + (data.fix ? 'Ja ✔️' : 'Nein ❌');
        })
        .catch(err => {
            console.error('Fehler beim Laden der Live-Daten:', err);
            document.getElementById('live-gps').textContent = 'GPS: Fehler 😢';
            document.getElementById('live-fix').textContent = 'Fix: Fehler 😢';
        });
}

// Alle 5 Sekunden abrufen
setInterval(ladeLiveDaten, 5000);
// Und direkt einmal beim Start
ladeLiveDaten();
</script>


</body>
</html>
