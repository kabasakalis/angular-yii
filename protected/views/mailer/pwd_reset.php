<center>
    <p style="font-size: 25px; margin-left: auto;  margin-right: auto;width:580px;">
        Hello <?php echo $username;?>,you requested a password reset.
        Please click on  the link below in order to perform the reset.<br>
        If you did not ask for a password reset,please ignore this email.
    </p>
    <div style=" display: block;
                               -moz-border-radius:3px;
                               -webkit-border-radius:3px;
                                background-color:#75078a;
                                 border-radius:3px;
                                 margin-left: auto;
                                  margin-right: auto;
                                 width:180px;
                                ">
        <a style="color:#ffdead; font-size: 40px;text-decoration: none" href="<?php echo $reset_url;?>"
           target="_blank">Reset Password</a>
    </div>
</center>
