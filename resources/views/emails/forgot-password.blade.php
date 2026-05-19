@extends('emails.layouts.master')

@section('title', 'Password Reset OTP')

@section('content')

<h2
    style="
        margin-top:0;
        margin-bottom:16px;
        font-size:28px;
        color:#0F172A;
    "
>
    Password Reset OTP
</h2>

<p
    style="
        color:#475569;
        font-size:16px;
        line-height:26px;
    "
>
    Use the OTP below to reset your password.
</p>

<div
    style="
        background:#EEF2FF;
        border:2px dashed #4F46E5;
        border-radius:14px;
        padding:22px;
        text-align:center;
        margin:30px 0;
    "
>

    <span
        style="
            font-size:38px;
            font-weight:800;
            color:#4F46E5;
            letter-spacing:10px;
        "
    >
        {{ $otp }}
    </span>

</div>

<p
    style="
        color:#64748B;
        font-size:15px;
    "
>
    OTP expires in 20 minutes.
</p>

@endsection