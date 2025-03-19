<?php
// Load environment variables
require_once __DIR__ . '/load_env.php';

// Get allowed origins from .env
$allowedOrigins = explode(',', getenv('ALLOWED_ORIGIN') ?: '*');
$allowedOrigins = array_map('trim', $allowedOrigins);

// Get request's Origin, fallback to Referer
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';

// If no Origin/Referer, assume it's a direct browser access (not CORS)
if (!$requestOrigin) {
    $isDirectAccess = true;
} else {
    $isDirectAccess = false;
    $requestOrigin = parse_url($requestOrigin, PHP_URL_HOST); // Extract domain only
}

// If it's a direct access (like opening in a browser), skip CORS checks
if (!$isDirectAccess) {
    // Check if the request's origin is allowed
    if (in_array('*', $allowedOrigins)) {
        header("Access-Control-Allow-Origin: *");
    } elseif (in_array($requestOrigin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: http://$requestOrigin");
    } else {
        header("HTTP/1.1 403 Forbidden");
        exit("CORS Error: Origin not allowed");
    }

    // Set other CORS headers
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}
// var_dump($requestOrigin);
// die();
// Serve the requested HTML file or default to index.html
$page = $_GET['gateway'] ?? 'index'; // Default to 'index.html' if no parameter
if (isset($_GET['gateway'])) {
    $filePath = __DIR__ . "/pages/$page.html"; // Ensure this path exists

    // Prevent path traversal attacks
    if (strpos(realpath($filePath), realpath(__DIR__ . "/pages")) !== 0 || !file_exists($filePath)) {
        header("HTTP/1.1 404 Not Found");
        exit("Error: Page not found");
    }

    // Serve HTML file
    header("Content-Type: text/html; charset=UTF-8");
    return readfile($filePath);
}
$originPath = htmlspecialchars($_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]));
$origin = htmlspecialchars($_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title>Popup-API Documentation</title>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@materializecss/materialize@2.0.2-alpha/dist/css/materialize.min.css">

    <!-- Compiled and minified JavaScript -->
    <script
        src="https://cdn.jsdelivr.net/npm/@materializecss/materialize@2.0.2-alpha/dist/js/materialize.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        nav .brand-logo {
            margin-left: 16px;
        }

        .sidenav {
            z-index: 9999;
        }

        /* on mobile */
        @media only screen and (max-width: 600px) {
            nav .brand-logo {
                margin-left: 0;
            }
        }


        #map {
            height: 240px;
        }

        code {
            font-family: Consolas, "courier new";
            color: rgb(192, 192, 192);
            padding: 2px;
            font-size: 105%;
        }
    </style>
    <link rel="stylesheet" href="assets/css/prism.css">
    <script src="assets/js/prism.js"></script>
</head>

<body>
    <header>
        <div class="navbar-fixed">
            <nav>
                <div class="nav-wrapper">
                    <a href="/popup-api" class="brand-logo">Popup Docs</a>
                    <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                    <ul id="nav-mobile" class="right hide-on-med-and-down">
                        <li><a href="#popcam">Camera</a></li>
                        <li><a href="#popqr">QR-Reader</a></li>
                        <li><a href="#poploc">Geolocation</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <ul class="sidenav" id="mobile-nav">
            <li><a href="#popcam">Camera</a></li>
            <li><a href="#popqr">QR-Reader</a></li>
            <li><a href="#poploc">Geolocation</a></li>
        </ul>
    </header>
    <main>
        <div class="container">
            <div class="section">
                <div class="row">
                    <div class="col s4">
                        <div class="center">
                            <i class="material-icons medium blue-text">flash_on</i>
                            <p class="promo-caption">Speeds up development</p>
                            <p class="light center">Most of the heavy lifting is done for you to provide a default
                                stylings
                                that
                                incorporate our custom components. We also refined animations and transitions to provide
                                a
                                smoother experience for developers.</p>
                        </div>
                    </div>
                    <div class="col s4">
                        <div class="center">
                            <i class="material-icons medium blue-text"">group</i>
                            <p class=" promo-caption">User Experience Focused</p>
                                <p class="light center">By utilizing elements and principles of Material Design, we were
                                    able to
                                    create a framework that focuses on User Experience.</p>
                        </div>
                    </div>
                    <div class="col s4">
                        <div class="center">
                            <i class="material-icons medium blue-text"">settings</i>
                            <p class=" promo-caption">Easy to work with</p>
                                <p class="light center">We have provided detailed documentation as well as specific code
                                    examples to
                                    help new users get started.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="popcam" class="section">
                <div class="row">
                    <div class="col s12">
                        <p class="flow-text bold">Popup Camera</p>
                        <img id="parent-img" class="responsive-img" src="https://picsum.photos/200" alt="result" />
                        <button type="button" id="popup-camera" class="btn">Try</button>
                        <p class="flow-text light">Calling popup window</p>
                        <pre class="language-javascript">
                            <code>
let params = `scrollbars=no,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=600,height=800`;
let popcam = window.open('<?= $originPath ?>?gateway=camera', 'popup-api', params);
if (!popcam) alert("Please allow popups for this website");
                            </code>
                        </pre>
                    </div>
                    <div class="col s12">
                        <p class="flow-text light">Setup event listener</p>
                        <pre class="code">
                            <code class="language-javascript">
let parentOutput = document.getElementById("parent-img");
                                
window.addEventListener("message", (event) => {
    // listen only to the popup origin
    if (event.origin !== "<?= $origin ?>") return;
    console.log('message', event)
    if (event.data.type == 'camera') {
        parentOutput.src = event.data.payload;
    }
}, false,);
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
            <div id="popqr" class="section">
                <div class="row">
                    <div class="col s12">
                        <p class="flow-text bold">Popup QR Reader</p>
                        <div class="row">
                            <div class="input-field col s8">
                                <input id="parent-text" type="text" class="validate">
                                <label for="parent-text">QR Result</label>
                            </div>
                            <div class="input-field col s4">
                                <button type="button" id="popup-qrcode" class="btn">Scan</button>
                            </div>
                        </div>
                        <p class="flow-text light">Calling Popup Window</p>
                        <pre class="code">
                            <code class="language-javascript">
let params = `scrollbars=no,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=600,height=800`;
let popcam = window.open('<?= $originPath ?>?gateway=qr-reader', 'popup-api', params);
if (!popcam) alert("Please allow popups for this website");
                            </code>
                        </pre>
                    </div>
                    <div class="col s12">
                        <p class="flow-text light">Setup event listener</p>
                        <pre class="code">
                            <code class="language-javascript">
let parentQrcode = document.getElementById("parent-text");

window.addEventListener("message", (event) => {
    // listen only to the popup origin
    if (event.origin !== "<?= $originPath ?>") return;
    console.log('message', event)
    if (event.data.type == 'qr-reader') {
        parentQrcode.src = event.data.payload;
    }
}, false,);
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
            <div id="poploc" class="section">
                <div class="row">
                    <div class="col s12">
                        <p class="flow-text bold">Popup Location</p>
                    </div>
                    <div id="map" class="col s12"></div>
                    <div class="col s12">
                        <button type="button" id="popup-location" class="btn">Get Location</button>
                        <p class="flow-text light">Calling popup window</p>
                        <pre class="code">
                            <code class="language-javascript">
let params = `scrollbars=no,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=600,height=800`;
let popcam = window.open('<?= $originPath ?>?gateway=geolocation', 'popup-api', params);
if (!popcam) alert("Please allow popups for this website");
                            </code>
                        </pre>
                    </div>
                    <div class="col s12">
                        <p class="flow-text light">Setup event listener</p>
                        <pre class="code">
                            <code class="language-javascript">
let map = L.map('map').setView([-6.2300848, 106.8136772], 17);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
                                
window.addEventListener("message", (event) => {
    // listen only to the popup host origin
    if (event.origin !== "<?= $origin ?>") return;
    console.log('message', event)
    if (event.data.type == 'geolocation') {
        let location = event.data.payload
        map.setView([location.coords.latitude, location.coords.longitude], 15)
    }
}, false,);
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="l6 s12">
                    <h5>Popup API</h5>
                    <p>Popup API documentation</p>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                &#169; 2025 Copyright TIF Territory 3
                <a class="right" href="/">Home</a>
            </div>
        </div>
    </footer>

    <script>
        let parentOutput = document.getElementById("parent-img");
        let parentQrcode = document.getElementById("parent-text");

        let parentMap = L.map('map').setView([-6.2300848, 106.8136772], 17);
        let parentMarker;

        let popupCamera = document.getElementById('popup-camera')
        let popupQrcode = document.getElementById('popup-qrcode')
        let popupLocation = document.getElementById('popup-location')
        let params = `scrollbars=no,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=600,height=800`;
        let popcam;

        popupCamera.addEventListener('click', (evt) => {
            console.log('click', evt);
            popcam = window.open('?gateway=camera', 'popup-api', params);
            if (!popcam) alert("Please allow popups for this website");
        })
        popupQrcode.addEventListener('click', (evt) => {
            console.log('click', evt);
            popcam = window.open('?gateway=qr-reader', 'popup-api', params);
            if (!popcam) alert("Please allow popups for this website");
        })
        popupLocation.addEventListener('click', (evt) => {
            console.log('click', evt);
            popcam = window.open('?gateway=geolocation', 'popup-api', params);
            if (!popcam) alert("Please allow popups for this website");
        })

        window.addEventListener("message", (event) => {
            console.log('message', event, "<?= $origin ?>")
            if (event.origin !== "<?= $origin ?>") return;
            switch (event.data.type) {
                case 'camera':
                    parentOutput.src = event.data.payload;
                    break;
                case 'qr-reader':
                    parentQrcode.value = event.data.payload;
                    break;
                case 'geolocation':
                    let location = event.data.payload
                    parentMap.flyTo([location.coords.latitude, location.coords.longitude], 15)
                    if (parentMarker) {
                        parentMarker.setLatLng([location.coords.latitude, location.coords.longitude])
                    } else {
                        parentMarker = L.marker([location.coords.latitude, location.coords.longitude]).addTo(parentMap)
                    }
                    break;
            }
        }, false, );

        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit()
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(parentMap);
            console.log('initialized');
        }, false);
    </script>
</body>

</html>