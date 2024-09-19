

$(document).ready(function () {
    $(".payment-successful").click(function () {

        var cardNumber1 = $("#card-number-1").val().trim();
        var cardNumber2 = $("#card-number-2").val().trim();
        var cardNumber3 = $("#card-number-3").val().trim();
        var cardNumber4 = $("#card-number-4").val().trim();

        var expiryMonth = $("#expiry-date-m").val().trim();
        var expiryYear = $("#expiry-date-y").val().trim();
        var cvc = $("#cvc").val().trim();
        var name = $("#name").val().trim();


        if (!cardNumber1 || !cardNumber2 || !cardNumber3 || !cardNumber4) {
            alert("Kart numarasının tüm alanlarını doldurmalısınız.");
            return;
        }
        if (!expiryMonth || !expiryYear) {
            alert("Son kullanma tarihinin tüm alanlarını doldurmalısınız.");
            return;
        }
        if (!cvc) {
            alert("CVV numarasını doldurmalısınız.");
            return;
        }
        if (!name) {
            alert("Kart sahibinin adını ve soyadını doldurmalısınız.");
            return;
        }

        var fullCardNumber = cardNumber1 + cardNumber2 + cardNumber3 + cardNumber4;

        $.ajax({
            url: "../../api/payment.php",
            type: "POST",
            dataType: "json", 
            data: {
                '2dsecure': !$('#3dsecure').is(':checked'), // 3D secure seçili değilse 2D'yi true yapar
                '3dsecure': $('#3dsecure').is(':checked'), // 3D secure seçili ise true
                'card-number': fullCardNumber,
                'expiry-month': expiryMonth.padStart(2, '0'),
                'expiry-year': expiryYear.padStart(2, '0'),
                'cvc': cvc.padStart(3, '0'),
                'name': name,
                id: "<?php echo $id; ?>"
            },
            success: function (response) {
                console.log("AJAX yanıtı:", response);

                if (response.status === 'success') {

                    if (response.hasOwnProperty('html')) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'https://sanalposprovtest.garantibbva.com.tr/servlet/gt3dengine'; //garanti istek

                        form.innerHTML = response.html;

                        document.body.appendChild(form);
                        form.submit();
                    }

                    $.ajax({
                        url: '../../api/get_payment_result.php', // İkinci AJAX çağrısının hedef URL'si
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            data: response.data, // İlk AJAX yanıtındaki data kısmı
                            securityType: response.data.securityType

                        },
                        success: function (response) {
                            alert("Veri başarıyla gönderildi: " + JSON.stringify(response));

                            // Gelen yanıt içinde `securityType` var mı kontrol et
                            if (response.hasOwnProperty('securityType') && response.securityType === '3d') {
                                var htmlContent = response.response;

                            }
                        },

                        error: function (xhr, status, error) {
                            alert("Veri gönderimi sırasında bir hata oluştu: " + error);
                        }
                    });
                } else {
                    alert("Ödeme işlemi başarısız: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("API çağrısı başarısız oldu: " + error);
            }
        });

    });

    function formatInput(event) {
        let value = event.target.value;
        value = value.replace(/\D/g, '');
        let maxLength = event.target.maxLength;
        if (value.length > maxLength) {
            value = value.slice(0, maxLength);
        }
        event.target.value = value;
    }
    document.getElementById('cvc').addEventListener('input', formatInput);
    document.getElementById('expiry-date-m').addEventListener('input', formatInput);
    document.getElementById('expiry-date-y').addEventListener('input', formatInput);
    document.getElementById('card-number-1').addEventListener('input', formatInput);
    document.getElementById('card-number-2').addEventListener('input', formatInput);
    document.getElementById('card-number-3').addEventListener('input', formatInput);
    document.getElementById('card-number-4').addEventListener('input', formatInput);
});
