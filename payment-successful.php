
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">


  <link rel="apple-touch-icon" href="<?php echo $settings['site_path'] . $settings['fav_icon']; ?>" />
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo $settings['site_path'] . $settings['fav_icon']; ?>" />

  <!-- Site Title -->
  <title>Ödeme Başarılı </title>

</head>

<body>
  <?php include 'header.php'; ?>

  <div class="container">
    
    
    <div class="successful-page col-md-8 m-auto mt-5">
        <div class="payment">
            <div class="payment_header">
               <div class="check"><i class="fa fa-check" aria-hidden="true"></i></div>
            </div>
            <div class="content mt-3">
               <h1>Ödeme Başarılı !</h1>
               <p class="px-2">🥳🥳 Harika! Ödemeniz başarılı bir şekilde tamamlanmıştır. 🥳🥳</p>
               <a href="./"><button class="wd-buttons rounded">Anasayfaya Dön</button></a>
               
               <div class="mt-5 mb-4"></div>
               
               <div class="account-black-color-in px-4 text-start">
        

          <div class="">

            <h5>Sepetiniz boş alışverişe devam edin</h5>
            <hr>
            <button class="btn text-white mt-3" style="background-color: #3c3c3c !important; padding: 15px;" onclick="window.location.href='./'">
              ALIŞVERİŞ YAPMAYA DEVAM ET <i class="fa-solid fa-arrow-right" style="color: #ffffff;"></i>
            </button>
          </div>
      </div>
      </div>
      </div>
    
    
   
    

    <?php include 'footer.php' ?>
  </div>




</body>

</html>