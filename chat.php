<?php

// Define your bot token
define('BOT_TOKEN', '7351220568:AAFNuN_wnh-sWRjppR0I26zdykJ98vFTQo8');

// Define the API URL
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

// The specific group chat ID
define('GROUP_CHAT_ID', '-1002233707621');

// Handle the incoming request
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    // Receive wrong update, must not happen
    exit;
}

// Check if the update contains a message
if (isset($update['message'])) {
    processMessage($update['message']);
}

function processMessage($message) {
    // Get the chat ID and the message text
    $chatId = $message['chat']['id'];
    $text = $message['text'];

    // Check if the message is the /gen command
    if ($text === '/gen') {
        // Create a new group invite link
        $inviteLink = createInviteLink();

        // Send the invite link to the user
        sendMessage($chatId, "Here is your invite link: $inviteLink");
    } else {
        // Send a message to the user indicating that the command is not recognized
        sendMessage($chatId, "Unknown command. Please use /gen to generate an invite link.");
    }
}

function createInviteLink() {
    // Define the parameters for creating a new chat invite link
    $params = [
        'chat_id' => GROUP_CHAT_ID,
        'expire_date' => time() + 3600, // Link valid for 1 hour
        'member_limit' => 1
    ];

    // Send the request to create the invite link
    $response = apiRequest('createChatInviteLink', $params);

    // Check if the request was successful
    if ($response['ok']) {
        return $response['result']['invite_link'];
    } else {
        return "Failed to create invite link.";
    }
}

function sendMessage($chatId, $text) {
    // Define the parameters for sending a message
    $params = [
        'chat_id' => $chatId,
        'text' => $text
    ];

    // Send the request to send the message
    apiRequest('sendMessage', $params);
}

function apiRequest($method, $params) {
    // Initialize a cURL session
    $ch = curl_init();

    // Set the cURL options
    curl_setopt($ch, CURLOPT_URL, API_URL . $method);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

    // Execute the cURL request
    $response = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    return json_decode($response, true);
}

?>