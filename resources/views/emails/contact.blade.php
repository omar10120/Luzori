<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #212529;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .field {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }
        .field-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .field-value {
            color: #6c757d;
        }
        .message-field {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            background-color: #6c757d;
            color: white;
            text-align: center;
            border-radius: 8px;
            font-size: 14px;
        }
        .highlight {
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Contact Form Submission</h1>
        <p>You have received a new message from your website contact form</p>
    </div>
    
    <div class="content">
        <div class="field">
            <div class="field-label">Name:</div>
            <div class="field-value">{{ $name }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">{{ $email }}</div>
        </div>
        
        @if($phone)
        <div class="field">
            <div class="field-label">Phone:</div>
            <div class="field-value">{{ $phone }}</div>
        </div>
        @endif
        
        <div class="field">
            <div class="field-label">Subject:</div>
            <div class="field-value">{{ $subject }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Message:</div>
            <div class="message-field">{{ $messageContent }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Submitted:</div>
            <div class="field-value">{{ $timestamp }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">IP Address:</div>
            <div class="field-value">{{ $ip_address }}</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This message was sent from your website contact form.</p>
        <p>You can reply directly to this email to respond to <span class="highlight">{{ $name }}</span>.</p>
    </div>
</body>
</html>
