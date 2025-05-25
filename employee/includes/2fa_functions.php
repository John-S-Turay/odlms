<?php
function generate2FASecret() {
    // Static secret for demo purposes
    return "DEMOSECRET123";
}

function verify2FACode($secret, $code) {
    // Accept any 6-digit code in demo mode
    return preg_match('/^\d{6}$/', $code);
}

function getQRCode($email, $secret) {
    // Return a placeholder QR code (base64 of a demo image)
    $demoQR = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACtWK6eAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9TpSIVBzuIOGSoThZERRy1CkWoEGqFVh1MbvqhNGlIUlwcBdeCgx+LVQcXZ10dXAVB8APE0clJ0UVK/F9SaBHjwXE/3t173L0DhGaVqWbPOKBqlpFOxMVcflUMvCKIEYQxICJTT2YWM/AcX/fw8fUuyrO8z/05BpWCyQCfSDzHdMMi3iCe2bR0zvvEYVaWFOJz4nGDLkj8yHXZ5TfOJYcFnhk2Mul54jCxWOrhK1jVTKZ4ijqqaTvlCxmOV8xZnVSojXjhU5fC7Gdsu5hnC9klJZJjBFUJQaCQhAqKKIihKNZNGIkW7TqR/6+Df0Kk1SiTBY5zCGBoQNkqMH/7Pjvrt1YyYT5LYCDQ+0rTQGjT5QrXe1jz3jQKAvwLbq9NY6y1J5AuE9sN2P0uAzwfoWbT9dY2AwGboD+N2te5jjT9HAL9K35YEwDNgXK/5aQ6wfQf0t4bW2d9QO+Th+9tZRZwHwN8pXW3sBAuB4h9d7O7t3R/t7x7nf9+AZ7VwJx0fZ3AAAAAElFTkSuQmCC";
    return $demoQR;
}
?>