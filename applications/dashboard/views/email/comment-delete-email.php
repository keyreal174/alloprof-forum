<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if (gte mso 9)|(IE)]>
    {literal}
    <style type="text/css">
        table {
            border-collapse: collapse;
        }
    </style>
    {/literal}
    <![endif]-->
    <style >
        @font-face {
            font-family: 'Chromatica';
            src:  url('https://res.cloudinary.com/deikols3z/raw/upload/v1618077529/Chromatica-Regular_q3kjyr.woff2') format('woff2'),
                    url('https://res.cloudinary.com/deikols3z/raw/upload/v1618077529/Chromatica-Regular_cl4cuh.ttf') format('ttf');
        }

        p, a, h2 {
            font-family: "Chromatica", sans-serif !important;
        }

        .normal-text {
            font-size: 15px;
            line-height: 18px;
            margin: 0;
            font-family: Chromatica!important;
        }

        .normal-text div {
            display: none!important;
        }

        .normal-text p {
            text-align: left;
            margin: 0!important;
        }

        .btn {
            padding: 13px 26px;
            margin-top: 45px;
            width: 176px;
            border-radius: 38px;
            border: 1px solid #000000;
            text-decoration: none;
        }

        .black--text {
            color: black!important;
        }

        .green--text {
            color: #05BF8E!important;
        }

        .social-icons a + a {
            margin-left: 16px;
        }

        .text-box {
            background: #F2F2F2;
            border-radius: 8px;
            padding: 24px;
            margin-top: 32px;
            margin-bottom: 32px;
            height: 102px;
            max-height: 102px;
            overflow: hidden;
            box-sizing: border-box;
        }

        .text-box p {
            overflow: hidden;
            height: 54px;
        }
    </style>
</head>

<body bgcolor="{$email.backgroundColor}"
    style='font-size: 16px;-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;width: 100% !important;height: 100%;margin: 0 !important;padding: 0;font-family: "Chromatica", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;color: {$email.textColor}' id="alloprof">
    <center class="wrapper"
        style="margin: 0;padding: 10px;box-sizing: border-box;font-size: 100%;width: 100%;table-layout: fixed;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: {$email.backgroundColor}">
        <div class="webkit" style="margin: 0 auto;padding: 0;box-sizing: border-box;font-size: 100%;max-width: 600px">

            <div style="width: 115px; height: 25px;">
                <a href="https://imgbb.com/"><img src="https://i.ibb.co/nkPWPv2/Alloprof-Logo.png" alt="Alloprof-Logo" border="0"></a>
            </div>

            <h2 style="font-size: 21px; line-height: 25px; margin: 32px;">Ce n'est pas grave, tu peux réessayer.</h2>

            <p class="normal-text">Bonjour, <?php echo ($this->Data["email"]["username"]) ?>! <br>
            Malheureusement, ton explication a été refusée pour la raison suivante : <br><br>
            <i><?php echo ($this->Data["email"]["reason"]) ?></i></p>

            <p class="normal-text" style="margin-top: 32px;">Voici l'explication que tu avais proposée :</p>

            <div class="text-box">
                <div class="normal-text">
                    <?php echo ($this->Data["email"]["boxtext"]) ?>
                </div>
            </div>

            <p class="black--text" style="margin-top: 32px; margin-bottom: 64px;">Retourne sur la <a href="https://www.alloprof.qc.ca/zonedentraide" class="green--text">Zone d’entraide</a> et proposes-en une nouvelle! <br><br>
            À tout de suite!</p>

            <img src="https://i.ibb.co/qNxsqk3/Illustraiton.png" alt="Illustraiton" border="0">
            <div style="background-color: #05BF8E; height: 4px; width: 90%; margin-bottom: 24px;"></div>

            <a href="https://www.alloprof.qc.ca/zonedentraide" class="black--text" style="font-size: 12px;"><i>Se désabonner</i></a>

            <p class="green-text" style="text-decoration: none; font-size: 12px; margin-top: 40px;margin-bottom: 28px; color: #05BF8E!important;">Suivez nous sur les réseaux sociaux : </p>

            <div class="social-icons">
                <a href="https://www.facebook.com/alloprof"><img src="https://i.ibb.co/Q6r5Qj1/Facebook-Icon.png" alt="Facebook-Icon" border="0"></a>
                <a href="https://www.instagram.com/alloprof"><img src="https://i.ibb.co/QpMwC2W/Instagram.png" alt="Instagram" border="0" width="32px" height="32px"></a>
                <a href="https://www.youtube.com/user/BV2ALLOPROF"><img src="https://i.ibb.co/k15h7Yk/Youtube-Icon.png" alt="Youtube-Icon" border="0"></a>
                <a href="https://twitter.com/alloprof"><img src="https://i.ibb.co/0VYhBmf/Twitter-Icon.png" alt="Twitter-Icon" border="0"></a>
            </div>
        </div>
    </center>
</body>

</html>
