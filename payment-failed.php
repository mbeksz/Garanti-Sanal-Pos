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
            <div class="payment_header payment_header2">
               <div class="check"><i class="fa-solid fa-x"></i></div>
            </div>
            <div class="content mt-3">
               <h1>Ödeme Başarısız !</h1>
               <p class="px-2">😞😞 Malesef! Ödemeniz gerçekleştirilemedi. 😞😞</p>
               <h4>Sipariş İptal Nedeni</h4>
               <p class="px-2">Siparişiniz, kartınızın internet alışverişine kapalı olması dolayısıyla bankanız tarafından reddedilmiştir. Lütfen kartınızı internet alışverişine açmayı deneyiniz.</p>
               
               <a href="./"><button class="wd-buttons rounded">Anasayfaya Dön</button></a>
               
               <div class="mt-5 mb-4"></div>
               
               <div class="account-black-color-in px-4 text-start">
          <div class="mb-3">
            <h4 class="mb-4 text-start mbl-none">Sipariş Özeti</h4>
            <h1 class="mb-4 text-start web-none mbl-mt-xl">Sipariş Özeti</h1>
            <hr>
            <div class="product-summary">
              <div>
                  <div class="product-item align-items-start">
                    <div class="product-image">

              </div>
              <hr>
            </div>
            <hr>
            <p>Ürün Toplamı: <span><?php echo $productTotalCost ?></span>₺</p>
            <p>Kargo Ücreti: <span id="price"><?php echo $cargoPrice ?></span>₺</p>
            <p>Genel Toplam: <span id="total_price"><?php echo $totalPrice ?></span>₺</p>
            <p>Tahmini teslimat süresi: <span id="delivery_time"><?php echo $delivery_time ?></span></p>
          </div>
          

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



  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>


</body>

</html>