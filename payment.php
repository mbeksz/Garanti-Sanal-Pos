<div class="screen flex-center">
          <div class="popup flex flex-column-reverse p-10">
            <div class="close-btn pointer flex-center p-sm">
              <i class="ai-cross"></i>
            </div>
            <!-- CARD FORM -->
            <div class="flex-fill flex-vertical">
              <div class="header flex-between flex-vertical-center">
                <div class="flex-vertical-center">
                  <!--<i class="fa-solid fa-money-bill"></i>-->
                  <span class="title">
                  <span>Ödeme Sayfası</span>
                  </span>
                </div>
                <div class="timer" data-id="timer">
                  <span>0</span><span>5</span>
                  <em>:</em>
                  <span>0</span><span>0</span>
                </div>
              </div>
              <div class="card-data flex-fill flex-vertical">

                <!-- Card Number -->
                <div class="flex-between flex-vertical-center">
                  <div class="card-property-title">
                    <strong>Kart Numarası</strong>
                    <span>16 haneli kart numaranızı girin</span>
                  </div>
                </div>

                <!-- Card Field -->
                <div class="flex-between">
                  <div class="card-number flex-vertical-center flex-fill">
                    <div class="card-number-field flex-vertical-center flex-fill">
                      <input class="numbers" id="card-number-1" maxlength="4" type="number" min="1" max="9999" placeholder="0000">-
                      <input class="numbers" id="card-number-2" maxlength="4" type="number" placeholder="0000">-
                      <input class="numbers" id="card-number-3" maxlength="4" type="number" placeholder="0000">-
                      <input class="numbers" id="card-number-4" maxlength="4" type="number" placeholder="0000" data-bound="carddigits_mock" data-def="0000">
                    </div>
                    <i class="ai-circle-check-fill size-lg f-main-color"></i>
                  </div>
                </div>

                <!-- Expiry Date -->
                <div class="flex-between">
                  <div class="card-property-title">
                    <strong>Son kullanma tarihi</strong>
                    <span>Kartın son kullanma tarihini girin</span>
                  </div>
                  <div class="card-property-value flex-vertical-center">
                    <div class="input-container half-width">
                      <input class="numbers" id="expiry-date-m" maxlength="2" data-bound="mm_mock" data-def="00" type="number" min="1" max="12" step="1" placeholder="AA">
                    </div>
                    <span class="m-md">/</span>
                    <div class="input-container half-width">
                      <input class="numbers" id="expiry-date-y" maxlength="2" data-bound="yy_mock" data-def="01" type="number" min="23" max="99" step="1" placeholder="YY">
                    </div>
                  </div>
                </div>

                <!-- CCV Number -->
                <div class="flex-between">
                  <div class="card-property-title">
                    <strong>CVV Numarası</strong>
                    <span>Kartın arkasındaki CVV kodunu girin</span>
                  </div>
                  <div class="card-property-value">
                    <div class="input-container">
                      <input id="cvc" type="number" pattern="\d{1,3}" maxlength="3" placeholder="XXX">
                      <i id="cvc_toggler" data-target="cvc" class="ai-eye-open pointer"></i>
                    </div>
                  </div>
                </div>


                <!-- Name -->
                <div class="flex-between">
                  <div class="card-property-title">
                    <strong>Kart Sahibinin Adı Soyadı</strong>
                    <span>Kart sahibinin adını yazın</span>
                  </div>
                  <div class="card-property-value">
                    <div class="input-container">
                      <input id="name" data-bound="name_mock" data-def="İSİM SOYİSİM" type="text" class="uppercase" placeholder="Ad Soyad">
                      <i class="ai-person"></i>
                    </div>
                  </div>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="3dsecure">
                  <label class="form-check-label" for="3dsecure">
                    3D ile ödemek istiyorum
                  </label>
                </div>

              </div>
              <div class="action flex-center">
                <button class="dark-button pointer payment-successful">Öde</button>
              </div>
            </div>

            <!-- SIDEBAR -->
            <div class="card-mockup flex-vertical">
              <div class="flex-fill flex-between">
                <i class="ai-bitcoin-fill size-xl f-secondary-color"></i>
                <i class="ai-wifi size-lg f-secondary-color"></i>
              </div>
              <div>
                <div id="name_mock" class="size-md pb-sm uppercase ellipsis">İsim Soyisim</div>
                <div class="size-md pb-md">
                  <strong>
                    <span class="pr-sm">
                      &#x2022;&#x2022;&#x2022;&#x2022;
                    </span>
                    <span id="carddigits_mock">0000</span>
                  </strong>
                </div>
                <div class="flex-between flex-vertical-center">
                  <strong class="size-md">
                    <span id="mm_mock">00</span>/<span id="yy_mock">01</span>
                  </strong>

                </div>
              </div>
              <ul class="purchase-props">
                <li class="d-flex gap-2">
                  <span>Şirket</span>
                  <strong>Şirket İsmi</strong>
                </li>
                <li class="d-flex gap-2">
                  <span>Sipariş No</span>
                  <strong><?php echo $id ?></strong>
                </li>

              </ul>
              <div class="flex-fill flex-vertical">
                <div class="total-label f-secondary-color">Ödenecek tutar</div>
                <div>
                  <strong><?php echo $totalPrice ?></strong>
                  <small><span class="f-secondary-color">₺</span></small>
                </div>
              </div>
            </div>
            <!--</div>-->
          </div>
        </div>
