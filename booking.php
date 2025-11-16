<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'casa_de_fernandes';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$table = 'bookings';

function clean($v) {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function html_header($title) {
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='utf-8'>
    <title>$title - Casa de Fernandes</title>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <style>body {font-family:Lato,Arial,sans-serif; background:#f8f6f2; color:#534832; margin: 3em; text-align:center;}</style>
    </head><body>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $name = clean($_POST['guestName']);
    $phone = clean($_POST['phoneNumber']);
    $email = clean($_POST['email']);
    $checkin = clean($_POST['checkinDate']);
    $checkout = clean($_POST['checkoutDate']);
    $guests = intval($_POST['numGuests'] ?? 0);
    $roomType = clean($_POST['roomType']);
    $services = '';
    if (isset($_POST['services'])) {
        $services = is_array($_POST['services']) ? implode(',', array_map('clean', $_POST['services'])) : clean($_POST['services']);
    }
    $special = clean($_POST['specialRequests']);

    if (strlen($name) < 2) $errors[] = "Please enter your full name.";
    if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Please enter a valid 10-digit phone number.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (!$checkin) $errors[] = "Please select a check-in date.";
    if (!$checkout) $errors[] = "Please select a check-out date.";
    if ($checkin && $checkout && strtotime($checkout) <= strtotime($checkin)) $errors[] = "Check-out date must be after check-in date.";
    if (!$guests || $guests < 1) $errors[] = "Please select the number of guests.";
    if (!$roomType) $errors[] = "Please select a room type.";

    if (count($errors) === 0) {
        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $sql = "INSERT INTO $table (name, phone, email, checkin, checkout, guests, room_type, services, special, created)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$name, $phone, $email, $checkin, $checkout, $guests, $roomType, $services, $special]);

            if ($success) {
                $_SESSION['booking_submitted'] = true;
                $_SESSION['booking_data'] = [
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'checkin' => $checkin,
                    'checkout' => $checkout,
                    'guests' => $guests,
                    'roomType' => $roomType,
                    'services' => $services,
                    'special' => $special,
                    'timestamp' => date('Y-m-d H:i:s'),
                ];

                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'fetch') {
                    header('Content-Type: application/json');
                    echo json_encode(["success" => true, "msg" => "Booking successfully submitted!"]);
                    exit;
                } else {
                    html_header("Booking Successful");
                    echo "<h2>Thank you, " . htmlspecialchars($name) . "!</h2>";
                    echo "<p>Your booking was received successfully.</p>";
                    echo "<p>We have your check-in date as " . htmlspecialchars($checkin) . ".</p>";
                    echo "<a href='index.html' style='color:#7b8466;'>Back to Home</a>";
                    echo "</body></html>";
                    exit;
                }
            } else {
                $errors[] = "Failed to save your booking, please try again.";
            }
        } catch (PDOException $e) {
            error_log("DB Error at booking.php: " . $e->getMessage());
            $errors[] = "A server error occurred. Please try again later.";
        }
    }

    if (count($errors) > 0) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='fetch') {
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "msg" => implode("<br>", $errors)]);
            exit;
        } else {
            html_header("Booking Failed");
            echo "<h2>Booking Failed</h2><ul>";
            foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>";
            echo "</ul><a href='index.html' style='color:#7b8466;'>Back to Home</a></body></html>";
            exit;
        }
    }
} else {
    html_header('Invalid Access');
    echo "<h2>No Booking Submitted</h2><p>Please submit your booking from the homepage.</p>";
    echo "<a href='index.html' style='color:#7b8466;'>Return to Home</a></body></html>";
    exit;
}
?>
