<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Flairm CRM')
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f1f5f9;
        font-family: Arial, sans-serif;
    ">

    <table
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="padding: 40px 20px;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                    max-width: 600px;
                    background-color: #ffffff;
                    border-radius: 18px;
                    overflow: hidden;
                    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
                ">

                    <!-- Header -->
                    <tr>
                        <td
                            align="center"
                            style="
                            background: linear-gradient(
                                90deg,
                                #4F46E5,
                                #2563EB
                            );
                            padding: 30px 20px;
                        ">

                            <h1
                                style="
                                margin: 0;
                                color: #ffffff;
                                font-size: 30px;
                                font-weight: 800;
                                letter-spacing: 0.5px;
                            ">
                                Flairm CRM
                            </h1>

                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td
                            style="
                            padding: 40px 32px;
                        ">

                            @yield('content')

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            align="center"
                            style="
                            background-color: #F8FAFC;
                            border-top: 1px solid #E2E8F0;
                            padding: 24px;
                        ">

                            <p
                                style="
                                margin: 0 0 8px;
                                color: #64748B;
                                font-size: 14px;
                                line-height: 22px;
                            ">
                                Thank you for using Flairm CRM
                            </p>

                            <p
                                style="
                                margin: 0;
                                color: #94A3B8;
                                font-size: 13px;
                            ">
                                © {{ date('Y') }} Flairm CRM.
                                All rights reserved.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>