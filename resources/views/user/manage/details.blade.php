<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-200"><?= __tr('User Details') ?></h1>
</div>
<!-- Page Heading -->

<!-- User details list start -->
<ul class="list-group">
    <!-- Full Name -->
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= __tr('Full Name') ?>
        <span class="float-right"><?= $userDetails['full_name'] ?></span>
    </li>
    <!-- Full Name -->
    <!-- Email -->
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= __tr('Email') ?>
        <span class="float-right"><?= $userDetails['email'] ?></span>
    </li>
    <!-- /Email -->
    <!-- Username -->
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= __tr('Username') ?>
        <span class="float-right"><?= $userDetails['username'] ?></span>
    </li>
    <!-- /Username -->
    <!-- Designation -->
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= __tr('Designation') ?>
        <span class="float-right"><?= $userDetails['designation'] ?></span>
    </li>
    <!-- /Designation -->
    <!-- Mobile Number -->
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= __tr('Mobile Number') ?>
        <span class="float-right"><?= $userDetails['mobile_number'] ?></span>
    </li>
    <!-- /Mobile Number -->
</ul>
<!-- /User details list end -->