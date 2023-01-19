<?php 

// Site Bildir project by Metin Yeşil
// Kurulum: İhtiyacınız olan, bir telegram grubu, bir twitter hesabı ve bir mysql veritabanı
// Aşağıda boş bıraktığım yerleri kendi bilgileriniz ile değiştirin ve CronJob ile dosyamıza 2 dakikada bir ayarını verin.
// Not: Twitter bağlantısı için, lütfen iftt.com'dan hesap açınız ve oradan aynı şablonu kullanarak sistemi başlatın.
// Uyarı: Her kullanıcı, kendi kullanımından sorumludur, hiçbir sorumluluk kabul edilemez.


// USOM'a bağlanıp sitemizi çekiyoruz
$data = simplexml_load_file('https://www.usom.gov.tr/url-list.xml');

$list = 'url-list';
$info = 'url-info';

$id = $data->$list->$info->id;
$urlz = $data->$list->$info->url;
$date = $data->$list->$info->date;


echo $id;
echo '<br>'.$urlz;
echo '<br>'.$date;
$adres = 'https://www.usom.gov.tr/adres/'.$id;

try{//hata varmı diye kontrol mekanizması.

        $baglanti=new PDO("mysql:host=localhost;dbname=dbadi","dbkadi","dbsifre");//bağlantı yaptık
        $ara=$baglanti->query("select * from liste where link like '$urlz' ");// Sayfa yenilendiğinde gelen bağlantı db'de var mı sorgusu yaptık
        $miktar=$ara->rowCount();//verilerin hepsini saydırdık.
        if($ara){//eğer veri çekildiyse
            if($miktar>0){
                foreach($ara as $al){//foreach $arada ki tüm verileri tek tek $al değişkenine aktaracak

                        echo $al["link"]." adlı site zaten bildirildi. <br />";//Eğer var ise "bildirildi" olarak index sayfamıza yazdırdık.
                    }
            }else{
                // yoksa, aşağıdaki işlemleri yaptırdık.
                echo "Bu Site Veritabanında bulunamadı.";
                $stmt=$baglanti->prepare("INSERT INTO liste SET link=? ");
                $sql=$stmt->execute([$urlz]);
                $token = "Your Telegram Chat Token";
                  $mesaj = "❌ Bir Site Yasaklandı!
🌐 Site URL: ".$urlz."
Kaynak: USOM ☑️
Tarih: ".$date."
Detaylar: ".$adres."";
                  $parametre= array(
                  'chat_id' => "Your Telegram Chat Id",
                  'text' => $mesaj ,
                  );
                  $ch = curl_init();
                  $url = "https://api.telegram.org/bot".$token."/sendmessage";
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_HEADER, false);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_POST, 1);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $parametre);
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                  $result = curl_exec($ch);                  
       
// Ifttt.com ile twitter üzerinden tweet atmak için yapılan cURL bağlantısı
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'Your IFTTT Token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"value1\":\"$urlz\",\"value2\":\"$date\",\"value3\":\"$adres\"}");

$headers = array();
$headers[] = 'Content-Type: application/json';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);



                }
            }
    }catch (PDOException $h) {

        $hata=$h->getMessage();

        echo "<b>HATA VAR :</b> ".$hata;//bağlantı hatası olursa.hata var yaz.

    }

 ?>
 
