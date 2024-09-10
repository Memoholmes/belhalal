<?php
    $webSiteName = getStoreSettings('name');
?>
<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <!-- Email Body -->
                <tr>
                    <td class="email-body" width="570" cellpadding="0" cellspacing="0">
                        <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0"
                            role="presentation">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    <div class="f-fallback">
                                        Dear <?= e($fullName) ?>,
                                        <br>
                                        <p>
                                            Welcome to <?= $webSiteName ?>! We are thrilled to have you join our community of like-minded individuals who are looking for love and companionship.
                                        </p>

                                        <p>
                                            At <?= $webSiteName ?>, we believe that everyone deserves a chance to find their perfect match, and we are dedicated to helping you do just that. Our platform is designed to make it easy and enjoyable to connect with other singles who share your interests, values, and goals.
                                        </p>
                                        <p>
                                            Whether you are new to online dating or have been using dating websites for years, we are confident that you will find <?= $webSiteName ?> to be a refreshing and rewarding experience. Our team is committed to providing you with the best possible service, and we are always here to answer your questions and offer support along the way.
                                        </p>

                                        <p>
                                            To get started, simply complete your profile and start browsing through our members. You can search for matches based on a variety of criteria, such as age, location, interests, and more. When you find someone who catches your eye, you can send them a message and start getting to know each other.
                                        </p>

                                        <p>
                                            We hope that you will find your soulmate on <?= $webSiteName ?>, and we look forward to seeing you around the site.
                                        </p>
                                    </div>
                                    <p class="f-fallback sub align-center">
                                        <br>Best regards,
                                        <br>The <?= $webSiteName ?> Team.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
