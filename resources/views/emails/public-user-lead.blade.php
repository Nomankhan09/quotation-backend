@extends('emails.layouts.master')

@section('title', 'New Signup Lead')

@section('content')

<h2
    style="
        margin-top:0;
        color:#0F172A;
    "
>
    New User Signup
</h2>

<p>
    A new user submitted the signup form.
</p>

<table
    width="100%"
    cellpadding="8"
    cellspacing="0"
    style="
        border-collapse:collapse;
        margin-top:20px;
    "
>

<tr>
    <td><strong>Name</strong></td>
    <td>
        {{ $publicUser->first_name }}
        {{ $publicUser->last_name }}
    </td>
</tr>

<tr>
    <td><strong>Email</strong></td>
    <td>
        {{ $publicUser->email }}
    </td>
</tr>

<tr>
    <td><strong>Phone</strong></td>
    <td>
        {{ $publicUser->phone }}
    </td>
</tr>

<tr>
    <td><strong>Company</strong></td>
    <td>
        {{ $publicUser->company_name }}
    </td>
</tr>

<tr>
    <td><strong>Message</strong></td>
    <td>
        {{ $publicUser->message }}
    </td>
</tr>

</table>

@endsection