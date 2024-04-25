<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        *{
            margin: 0;
            padding: 0;
        }
        /* Global styles */
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
        }

        .greet{
            font-size: 14px;
        }

        .fst_para{
            font-size: 14px;
        }

        .thanks{
            font-size: 14px;
        }

        .help{
            font-size: 14px;
        }

        /* Header styles */
        .header {
            background-color: #256092;
            padding: 15px;
        }
        .header h1{
            font-size: 18px;
            text-align: center;
            color: #ffffff;
        }

        /* Footer styles */
        .footer {
            background-color: #68b28a;
            color: #f4f4f4;
            text-align: center;
            font-size: 14px;
            padding: 10px 0px;
        }

        .warm{
            font-size: 14px;
            font-weight: bolder;
            margin-top: 15px;
        }

        .team{
            font-size: 14px;
        }

        /* Button styles */
        .button {
            background-color: #3e9917;
            border-radius: 5px;
            display: block;
            width: max-content;
            color: #fff;
            display: inline-block;
            font-size: 14px;
            padding: 7px 20px;
            text-decoration: none;
            margin: 10px 0px;
        }
        .support{
            text-decoration: none;
            font-weight: bolder;
            color: #256092;
        }
    </style>
</head>
<body>
    <div class="header" style="padding: 15px;">
        <h1 style="color: #ffffff; text-align: center;margin: 0px !important;">UGA1Tech - Employee Onboarding</h1>
    </div>
    <div class="body" style="padding: 15px 15px;">
        <h3 class="greet">
            Greetings,
        </h3>
        <p class="fst_para">
            You are registerd on UGA1HRM.
        </p>
        <a href="" class="button">Go To Website</a>
        <div class="help">
            If you experience any issues, please feel free to contact at:
            <a href="" class="support">{{$url }}</a>.
        </div>
        <p class="warm">Regards,</p>
        <p class="team">UGA1Tech</p>

    </div>
    <div class="footer" style="width: 100%">
        <p style="color: #ffffff">All rights reserved</p>
    </div>
</body>
</html>
