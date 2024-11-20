<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content p {
            font-size: 16px;
            color: #333333;
            margin: 16px 0;
        }
        .otp {
            display: inline-block;
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            background-color: #f0f8ff;
            padding: 10px 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f9f9f9;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #777777;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Sensitive Information</h1>
        </div>
        <div class="content">
            <p>Hi,</p>
            <p>We received a request to verify your email address for Good Creator.</p>
            <p>Here is your One-Time Password (OTP):</p>
            <div class="otp">{{ $data['message'] }}</div>
            <p>If you did not request this, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 Good Creator. All rights reserved.</p>
            <p>Need help? Contact us at <a href="mailto:support@goodcreatr.com">support@goodcreatr.com</a></p>
        </div>
    </div>
</body>
</html>
