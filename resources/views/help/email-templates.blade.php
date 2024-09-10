@section('page-title', __tr("Email References"))
@section('head-title', __tr("Email References"))
@section('keywordName', strip_tags(__tr("Email References")))
@section('keyword', strip_tags(__tr("Email References")))
@section('description', strip_tags(__tr("Email References")))
@section('keywordDescription', strip_tags(__tr("Email References")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<?php $userStatus = request()->status; ?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-200">
        <?= __tr('Email References') ?>
    </h1>
</div>

<div class="row">
    <div class="col-xl-12">
        <!-- card -->
        <div class="card mb-4">
            <!-- card body -->
            <div class="card-body">
                <div class="lw-nav-content">
                    <table class="table table-hover">
                        <tr>
                            <th>{{ __tr("Email Templates") }}</th>
                            <th>{{ __tr("Path") }}</th>
                        </tr>
                        <tr>
                            <td>{{ __tr("Activation for app") }}</td>
                            <td>{{ __tr("/resources/views/emails/account/activation-for-app.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr("Account activation") }}</td>
                            <td>{{ __tr("/resources/views/emails/account/activation.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr("Forgot password for app") }}</td>
                            <td>{{ __tr("/resources/views/emails/account/forgot-password-for-app.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr("Login with otp") }}</td>
                            <td>{{ __tr("/resources/views/emails/account/login-with-otp.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr("New email activation") }}</td>
                            <td>{{ __tr("/resources/views/emails/account/new-email-activation.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr("Password reminder") }}</td>
                            <td>{{ __tr("/resources/views/emails/account/password-reminder.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr('Welcome') }}</td>
                            <td>{{ __tr("/resources/views/emails/account/welcome.blade.php") }}</td>
                        </tr>
                        <tr>
                            <td>{{ __tr('Contact') }}</td>
                            <td>{{ __tr("/resources/views/emails/contact.blade.php") }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>