<?php
$ch = curl_init('http://127.0.0.1:8000/api/v1/employees');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$postData = [
    'emp_id' => 'EMP-TEST-1235',
    'full_name' => 'Test Employee',
    'employment_type' => 'Full-Time',
    'joining_date' => '2026-06-17',
    'dob' => '',
];
// To simulate FormData with empty values for nulls omitted:
// Actually PHP curl with array is multipart/form-data
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer 24|p2qn8t3evKarNjVKA3EYjFlLsKWGcmBqkNq0U980e435a0b8'
]);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
