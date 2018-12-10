<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>信息提示</title>
        <style type="text/css">
            * {
                padding: 0;
                margin: 0;
            }
            body {
                background: #f9f9f9;
                font-family: '微软雅黑';
                color: #333;
                font-size: 16px;
            }
            .box {
                margin: 100px auto;
                width: 600px;
                background: #fff;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                border: 1px solid #ccc;
                padding: 10px;
                box-shadow: inset 0 0 3px #eee;
                min-height:150px;
            }
            .system-message { padding: 12px 24px; min-height:200px; }
            .system-message h1 {
                font-size: 36px;
                line-height: 40px;
                margin-bottom: 12px;
                text-shadow: 0 0 5px #9FB1BF;
                border-bottom:1px solid #ddd;
                padding-bottom:10px;
            }
            .jump { padding: 12px 24px; font-size:12px; }
            .jump a { color: #333; font-size:12px; }
            .system-message .success, .system-message .error {
                line-height: 1.8em;
                font-size: 16px
            }
            .system-message .detail {
                font-size: 12px;
                line-height: 20px;
                margin-top: 12px;
                display: none;
            }
            footer{ text-align:center; padding:5px 12px; color:#333; font-size:12px;border-top:1px solid #ddd;}
        </style>
    </head>
    <body>
        <div class="box">
            <div class="system-message">
                <?php if(isset($message)) {?>
                    <h1>温馨提示</h1>
                    <p class="success"><?php echo($message); ?></p>
                  <?php }else { ?>
				    <h1>温馨提示</h1>
                    <p class="error"><?php echo($error); ?></p>
                <?php } ?>
                <p class="detail"></p>                
            </div>
            
            <footer>©2018</footer>
        </div>
        
    </body>
</html>