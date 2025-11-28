<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>rConfig System Test Email</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            border: 1px solid #e4e4e4;
            border-radius: 5px;
            padding: 25px;
            background-color: #ffffff;
        }
        .header {
            border-bottom: 2px solid #0056b3;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 15px;
        }
        h1 {
            color: #0056b3;
            font-size: 24px;
            margin: 0;
            padding: 0;
        }
        .content {
            padding: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e4e4e4;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #0056b3;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ $message->embed(public_path('/images/brand/gradient_logo_brand_strap.svg')) }}" alt="rConfig Logo" class="logo">
            <h1>Email Configuration Test</h1>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>This is a test email from your <strong>rConfig</strong> system.</p>
            <p>If you're reading this message, your email configuration is working correctly! 🎉</p>
            
            <p>This confirms that rConfig can successfully:</p>
            <ul>
                <li>Connect to your mail server</li>
                <li>Send emails using your configured settings</li>
                <li>Deliver notifications to specified recipients</li>
            </ul>
            
            <a href="{{ url('/settings') }}" class="button">Return to Settings</a>
            
            <p>No further action is required.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message from rConfig - Network Configuration Management</p>
            <p>&copy; {{ date('Y') }} rConfig | <a href="https://www.rconfig.com">www.rconfig.com</a></p>
        </div>
    </div>
</body>

</html>