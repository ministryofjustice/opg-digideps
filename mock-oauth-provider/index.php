<?php

declare(strict_types=1);

$devDomainRegex = '/^https:\/\/([^.\/]+\.)?development\.sirius\.opg\.service\.justice\.gov\.uk\/oauth\/response\/?$/';

if (str_starts_with($_SERVER['REQUEST_URI'], '/authorize')) {
    if (isset($_POST['email'])) {
        $redirectUri = $_GET['redirect_uri'];

        if (!str_ends_with($_POST['email'], '@publicguardian.gov.uk')) {
            echo 'Invalid email address: must be on <code>publicguardian.gov.uk</code> domain';

            return;
        }

        if (str_starts_with($redirectUri, 'https://admin.digideps.local/') || str_starts_with($redirectUri, 'http://admin.digideps.local/')) {
            // continue
        } elseif (preg_match($devDomainRegex, $redirectUri)) {
            // continue
        } else {
            http_response_code(500);
            echo 'Invalid redirect URI';

            return;
        }

        $params = [
            'state' => $_GET['state'],
            'code' => base64_encode($_POST['email']),
        ];

        header("Location: " . $redirectUri . '?' . http_build_query($params));

        return;
    } else {
        include './login.html';

        return;
    }
}

if (str_starts_with($_SERVER['REQUEST_URI'], '/token')) {
    $email = base64_decode($_POST['code']);
    $idToken = base64_encode(json_encode(['email' => $email]));

    echo json_encode([
        'access_token' => 'my-access:' . $email,
        "id_token" => 'header.' . $idToken . '.signature'
    ]);

    return;
}

if (str_starts_with($_SERVER['REQUEST_URI'], '/graph/v1.0/me')) {
    $accessToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    $email = str_replace('Bearer my-access:', '', $accessToken);
    $identifier = explode('@', $email)[0];

    echo json_encode([
        "@odata.context" => 'https://graph.microsoft.com/v1.0/$metadata#users/$entity',
        "displayName" => $identifier,
        "givenName" => $identifier,
        "mail" => $email,
        "userPrincipalName" => $email,
        "id" => "10a08e2e-3ea2-4ce0-80cb-ddddd4b05ea6"
    ]);

    return;
}

http_response_code(404);
echo "Page not found";
