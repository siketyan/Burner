<?php
  require "api/autoload.php";
  use Abraham\TwitterOAuth\TwitterOAuth;
  
  if (!empty($_GET['i'])) {
    $url = $_GET['i'];
  } else {    
    $key = ""; // Consumer Key
    $csecret = ""; // Consumer Secret
    $token = ""; // Access Token
    $secret = ""; // Access Token Secret
    
    $user = htmlspecialchars($_GET['u']);
    $sql = new mysqli("localhost", "burner", ""/* MySQL Password */, "burner");
    $result = $sql->query("SELECT * FROM `icons` WHERE `name` = \"$user\" LIMIT 1");
    
    if ($result->num_rows != 1) {
      $api = new TwitterOAuth($key, $csecret, $token, $secret);
      $u = $api->get("users/show", ["screen_name" => $user]);
      $name = $u->screen_name;
      $icon = str_replace("normal", "400x400", $u->profile_image_url_https);
  
      $sql->query("INSERT INTO `icons`(`name`, `url`) VALUES(\"$name\", \"$icon\")");
      $result = $sql->query("SELECT * FROM `icons` WHERE `name` = \"$user\" LIMIT 1");
    }
    
    $url = $result->fetch_array()['url'];
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <style>
      body {
        background-color: black;
      }

      #output {
        width: 1280px;
        height: 720px;
        margin-top: 128px;
      }

      #buffer {
        display: none;
      }

      #back {
        width: 1280px;
        height: 600px;
        background-image: url("<?= $url ?>");
        background-size: auto 100%;
        background-repeat: no-repeat;
        background-position: center;
      }
    </style>
  </head>
  <body>
    <div id="back">
      <video id="video" style="display:none" autoplay>
        <source src="fire.mp4" type='video/mp4; codecs="h.264"' />
        <source src="fire.webm" type='video/webm; codecs="vp8"' />
      </video>
      <canvas id="output" width="1280" height="720"></canvas>
      <canvas id="buffer" width="1280" height="1440"></canvas>
    </div>

    <script>
      (function(){
        var outputCanvas = document.getElementById('output'),
          output = outputCanvas.getContext('2d'),
          bufferCanvas = document.getElementById('buffer'),
          buffer = bufferCanvas.getContext('2d'),
          video = document.getElementById('video'),
          width = outputCanvas.width,
          height = outputCanvas.height,
          interval;
          
        function processFrame() {
          buffer.drawImage(video, 0, 0);

          var image = buffer.getImageData(0, 0, width, height),
            imageData = image.data,
            alphaData = buffer.getImageData(0, height, width, height).data;
          
          for (var i = 3, len = imageData.length; i < len; i = i + 4) {
            imageData[i] = alphaData[i-1];
          }
          
          output.putImageData(image, 0, 0, 0, 0, width, height);
        }

        video.addEventListener('play', function() {
          clearInterval(interval);
          interval = setInterval(processFrame, 40)
        }, false);
        
        video.addEventListener('ended', function() {
          video.play();
        }, false);
      })();
    </script>
  </body>
</html>