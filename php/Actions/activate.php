<?php
require_once __DIR__ . '/../Conexion/db_conexion.php'; 

function redirect_with($url, $params = []) {
    if (!empty($params)) {
        $q = http_build_query($params);
        if (strpos($url, '?') === false) $url .= '?' . $q; else $url .= '&' . $q;
    }
    header("Location: $url");
    exit;
}

$email    = $_GET['email'] ?? '';
$rawToken = $_GET['token'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !ctype_xdigit($rawToken) || strlen($rawToken) !== 64) {
    redirect_with('/Index.html', ['error' => 'Invalid activation link.']);
}

$tokenHash = hash('sha256', $rawToken);

// 1) usuario por email
$sql  = "SELECT id, status FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$res  = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    redirect_with('/Index.html', ['error' => 'User not found.']);
}

// 2) token más reciente sin usar
$sql = "SELECT id, token, expires_at, used_at
        FROM email_verification_tokens
        WHERE user_id = ? AND used_at IS NULL
        ORDER BY created_at DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$resTok   = $stmt->get_result();
$tokenRow = $resTok->fetch_assoc();
$stmt->close();

if (!$tokenRow) {
    redirect_with('/Index.html', ['error' => 'Verification token not found or already used.']);
}

if (new DateTime() > new DateTime($tokenRow['expires_at'])) {
    redirect_with('/Index.html', ['error' => 'Verification link has expired.']);
}

// 3) comparar hash
if (!hash_equals($tokenRow['token'], $tokenHash)) {
    redirect_with('/Index.html', ['error' => 'Invalid token.']);
}

// 4) transacción: marcar token usado y activar usuario si estaba pending
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE email_verification_tokens SET used_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $tokenRow['id']);
    if (!$stmt->execute()) throw new Exception('Cannot mark token as used');
    $stmt->close();

    $stmt = $conn->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('i', $user['id']);
    if (!$stmt->execute()) throw new Exception('Cannot activate user');
    $stmt->close();

    $conn->commit();
    redirect_with('/Index.html', ['msg' => 'Account activated successfully. You can now log in.']);
} catch (Throwable $e) {
    $conn->rollback();
    redirect_with('/Index.html', ['error' => 'Activation failed. Please try again later.']);
}
