<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{$greeting}}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>
{{-- (900 Width Table Report EMail) --}}
<body
    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0 auto; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0"
        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #f3f3f3; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <tr>
            <td align="center" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                <table class="content" width="100%" cellpadding="0" cellspacing="0"
                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                    <tr>
                        <td class="header"
                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0; text-align: center;">
                            <a href="https://www.rconfig.com" class="alink"
                                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 19px; font-weight: bold; text-decoration: none;">
                                rConfig Download Report
                            </a>
                        </td>
                    </tr>
                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0"
                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #FFF; border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 900px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                        <img src="https://www.rconfig.com/images/rConfig_logos/new/blue/hex_logo_blue_horizontal_72.png" alt="rConfig Logo" title="rConfig Logo" style="display: block;  margin-left: auto; margin-right: auto; padding-bottom: 15px;"/>

                                        <h1
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 19px; font-weight: bold; margin-top: 0; text-align: left;">
                                            {{$greeting}}</h1>
                                        <p
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                            <b>{{$report_header->task_name}}</b> task ran successfully.
                                        </p>
                                        <h2
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 16px; font-weight: bold; margin-top: 0; text-align: left;">
                                            Task Information</h2>
                                            <table class="panel" width="100%" cellpadding="0" cellspacing="0"
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0 0 21px;">
                                            <tr>
                                                <td class="panel-content"
                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ededed; padding: 16px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                        <tr>

                                                            <tr>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    Report ID</td>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                {{$report_header->report_id}}</td>

                                                            </tr>
                                                            <tr>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    Task ID</td>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                {{$report_header->task_id}}</td>

                                                            </tr>
                                                            <tr>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    Task Name</td>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    {{$report_header->task_name}}</td>

                                                            </tr>
                                                            <tr>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    Task Description</td>
                                                                <td
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    {{$report_header->task_desc}}</td>

                                                            </tr>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <table class="action" align="center" width="100%" cellpadding="0"
                                            cellspacing="0"
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 30px auto; padding: 0; text-align: center; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                            <tr>
                                                <td align="center"
                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                        <tr>
                                                            <td align="center"
                                                                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                                <table border="0" cellpadding="0" cellspacing="0"
                                                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                                    <tr>
                                                                        <td
                                                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                                            <a href="{{ url('/config-reports/'.$report_header->report_id) }}" class="button button-blue"
                                                                                target="_blank" class="alink"
                                                                                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); color: #ffffff; display: inline-block; text-decoration: none; -webkit-text-size-adjust: none; background-color: #2d3748; border-top: 10px solid #2d3748; border-right: 18px solid #2d3748; border-bottom: 10px solid #2d3748; border-left: 18px solid #2d3748;">
                                                                                View Online</a>
                                                                        </td>
                                                                        <td
                                                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                                            <a  href="{{url('/config-reports')}}" class="button button-blue"
                                                                                target="_blank" class="alink"
                                                                                style=" margin-left: 20px; font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); color: #ffffff; display: inline-block; text-decoration: none; -webkit-text-size-adjust: none; background-color: #2d3748; border-top: 10px solid #2d3748; border-right: 18px solid #2d3748; border-bottom: 10px solid #2d3748; border-left: 18px solid #2d3748;">
                                                                                View all reports</a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <h2
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 16px; font-weight: bold; margin-top: 0; text-align: left;">
                                            Report Details</h2>
                                        <div class="table" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                            <table class="panel" width="100%" cellpadding="0" cellspacing="0"
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0 0 21px;">
                                            <tr>
                                                <td class="panel-content" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ededed; padding: 16px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0"  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                        <thead  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                            <tr>
                                                                <th  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; border-bottom: 1px solid #dadada; padding-bottom: 8px; text-align: left;">
                                                                    Device Name
                                                                </th>
                                                                <th  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; border-bottom: 1px solid #dadada; padding-bottom: 8px; text-align: left;">
                                                                    Device IP
                                                                </th>
                                                                <th  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; border-bottom: 1px solid #dadada; padding-bottom: 8px; text-align: left;">
                                                                    Device Status
                                                                </th>
                                                                <th  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; border-bottom: 1px solid #dadada; padding-bottom: 8px; text-align: left;">
                                                                    Commands Report <br/><small><i>Command - Line count</i></small>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody  style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">

                                                            @foreach ($task_details as $td)
                                                            <tr>
                                                                <td width="25%" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    {{$td->device_name}}
                                                                </td>
                                                                <td width="15%" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0;">
                                                                    {{$td->device_ip}}
                                                                </td>
                                                                <td width="10%" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; text-align: right;">
                                                                    @if ($td->device_connect_status === '0')
                                                                        <img src="{!! url('/images/times.png'); !!}" alt="Fail" title="Fail" style="display: block;  margin-left: auto; margin-right: auto; padding-bottom: 15px;"/>
                                                                    @else
                                                                        <img src="{!! url('/images/check.png'); !!}" alt="Success" title="Success" style="display: block;  margin-left: auto; margin-right: auto; padding-bottom: 15px;"/>
                                                                    @endif
                                                                </td>
                                                                <td width="50%" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0 0 10px;">
                                                                    @if(count($td->commands_and_status) > 0)
                                                                        @foreach($td->commands_and_status as $key=>$value)
                                                                            <span>{{$key}} - {{$value}}</span><br/>
                                                                        @endforeach
                                                                        {{-- {{ print_r($td->commands_and_status) }} --}}
                                                                    @else
                                                                        <span>No results</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @endforeach

                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        </div>

                                        <p
                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                            Regards,<br>
                                            rConfig</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0"
                                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0 auto; padding: 0; text-align: center; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
                                <tr>
                                    <td class="content-cell" align="center"
                                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                        <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 0; color: #aeaeae; font-size: 12px; text-align: center;">
                                         Copyright © 2010 - <?php echo date('Y'); ?> OS Informatics Ltd. T/A rConfig. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>