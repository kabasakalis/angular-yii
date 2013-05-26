<center>
    <p style="font-size: 25px; margin-left: auto;  margin-right: auto;width:580px;">
        Hello <?php echo $username;?>.In order to complete your registration,you have to activate your account.<br>
        Click on the link below.
    </p>
    <div style=" display: block;
                               -moz-border-radius:3px;
                               -webkit-border-radius:3px;
                                background-color:#1aa0ff;
                                 border-radius:3px;
                                 margin-left: auto;
                                  margin-right: auto;
                                 width:180px;
                                ">
        <a style="color:#ffdead; font-size: 40px;text-decoration: none" href="<?php echo $activation_url;?>"
           target="_blank">Activate</a>
    </div>
</center>
