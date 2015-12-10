<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width">
</head>
<body bgcolor="{$email.backgroundColor}" style='color: {$email.textColor};font-size: 16px;font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;padding: 0;margin: 0;text-align: left;line-height: 1.4;-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;width: 100% !important;height: 100%'>
<!-- start body -->
<table class="body-wrap" style='margin: 0;padding: 10px 10px 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};border-spacing: 0;font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;background-color: {$email.backgroundColor};width: 100%'><tr style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%">
<td class="container" style='margin: 0 auto !important;padding: 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;display: block !important;max-width: 600px !important;clear: both !important'>
      <div class="content" style="margin: 0 auto;padding: 20px 30px;box-sizing: border-box;font-size: 100%;background-color: {$email.containerBackgroundColor};max-width: 600px;display: block">
        <table style='margin: 0;padding: 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};border-spacing: 0;font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;width: 100%'><tr style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%">
<td style='margin: 0;padding: 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;background-color: {$email.containerBackgroundColor}'>
              {if $email.image}
                <div class="image-wrap center" style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%;margin-bottom: 10px;text-align: center">
                  {if $email.image.link}
                    <a href="{$email.image.link}" style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%">
                  {/if}
                  {if $email.image.source != ''}
                    <img class="center" src="{$email.image.source}" alt="{$email.image.alt}" style="max-width: 75%;margin: 0;padding: 0;box-sizing: border-box;font-size: 100%;text-align: center">
                  {elseif $email.image.alt != ''}
                    <h1 class="center" style='margin: 0;padding: 0;box-sizing: border-box;font-size: 36px;margin-top: 20px;margin-bottom: 5px;line-height: 1.4;font-weight: 300;color: {$email.textColor};font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;text-align: center;word-break: normal'>{$email.image.alt}</h1>
                  {/if}
                  {if $email.image.link}
                    </a>
                  {/if}
                </div>
                <hr style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%;background-color: {$email.textColor}">
              {/if}
              {if $email.title}<h1 class="center" style='margin: 0;padding: 0;box-sizing: border-box;font-size: 36px;margin-top: 20px;margin-bottom: 5px;line-height: 1.4;font-weight: 300;color: {$email.textColor};font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;text-align: center;word-break: normal'>{$email.title}</h1>{/if}
              {if $email.lead}<div class="lead center" style="margin: 0;padding: 0;box-sizing: border-box;font-size: 20px;margin-bottom: 15px;line-height: 1.2;text-align: center">{$email.lead}</div>{/if}
              <div class="message" style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%;margin-top: 10px;margin-bottom: 15px">{$email.message}</div>
              {if $email.button}
                <div class="button-wrap center" style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%;text-align: center">
                  <a href="{$email.button.url}" class="button" style="margin: 0;padding: 10px 20px;box-sizing: border-box;font-size: 100%;color: {$email.button.textColor};background-color: {$email.button.backgroundColor};text-decoration: none;text-align: center;font-weight: 700;cursor: pointer;display: inline-block">{$email.button.text}</a>
                </div>
              {/if}
            </td>
          </tr></table>
<!-- content end below -->
</div>
      <!-- container end below -->
    </td>
  </tr></table>
<!-- end body --><!-- start footer --><table class="footer-wrap" style='margin: 0;padding: 0px 10px 10px;box-sizing: border-box;font-size: 16px;color: {$email.textColor};border-spacing: 0;font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;background-color: {$email.backgroundColor};width: 100%'>
  {if $email.footer}
  <tr style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%">
<td class="container" style='margin: 0 auto !important;padding: 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;display: block !important;max-width: 600px !important;clear: both !important'>
      <div class="content" style="margin: 0 auto;padding: 20px 30px;box-sizing: border-box;font-size: 100%;background-color: {$email.button.backgroundColor};max-width: 600px;display: block">
        <table style='margin: 0;padding: 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};border-spacing: 0;font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;width: 100%'><tr style="margin: 0;padding: 0;box-sizing: border-box;font-size: 100%">
<td style='margin: 0;padding: 0;box-sizing: border-box;font-size: 16px;color: {$email.textColor};font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;font-weight: 300;text-align: left;line-height: 1.4;background-color: {$email.button.backgroundColor}'>
              <div class="footer center" style="margin: 0;padding: 0;box-sizing: border-box;font-size: 14px;color: {$email.button.textColor};text-align: center">{$email.footer}</div>
            </td>
          </tr></table>
</div>
    </td>
  </tr>
  {/if}
</table>
<!-- end footer -->
</body>
</html>
