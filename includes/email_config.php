<?php
return [
    // SMTP
    'smtp_host'   => 'smtp.gmail.com',
    'smtp_port'   => 587,                 // Use 465 for 'ssl'
    'smtp_user'   => 'renzrodriguez23@gmail.com',
    'smtp_pass'   => 'ixuj mwsg cuxt nvsv', // Use App Password, not Gmail password
    'smtp_secure' => 'tls',               // or 'ssl' if using port 465

    // From address
    'from_email'  => 'NGXT@gmail.com',
    'from_name'   => 'NGXT System Bot',

    // Who will receive registration alerts
    'reviewers'   => [
        'renzrodriguez23@gmail.com'            // You can use your same Gmail for now
    ],

    'contact_recipients' => [
    'renzrodriguez23@gmail.com'      // where contact inquiries go
    ],


    // Where reviewers will click to edit accounts (localhost version)
    'system_base_url' => 'http://localhost/ngxt',   // ✅ for local testing

    'edit_url_template' => 'http://localhost/ngxt/admin/partners.php?user_id=%d',
];
