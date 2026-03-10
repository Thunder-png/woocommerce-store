# WooCommerce Store Proje Yol Haritası

Bu doküman, `woocommerce-store` reposu için **tek kişiyle yönetilebilecek ama profesyonel seviyede kapsamlı** bir e-ticaret geliştirme planıdır. Amaç; projeyi sadece "kurulmuş bir WordPress sitesi" olmaktan çıkarıp **satışa hazır, güven veren, ölçülebilir, sürdürülebilir ve geliştirilebilir** bir WooCommerce mağazasına dönüştürmektir.

> Proje tipi: Direkt satış odaklı e-ticaret  
> Satış modeli: **m² başına fiyat + KDV + kargo**  
> Ödeme modeli: **Sanal POS + Havale/EFT**  
> Operasyon modeli: **Ödeme sonrası hazırlık + kargo çıkışı**  
> Geliştirme modeli: **WooCommerce + Child Theme + Custom Frontend + JS mantığı**

---

## 1. Proje Vizyonu ve Hedefler

### Ana hedefler
- Güven veren kurumsal bir e-ticaret sitesi kurmak
- Ürünleri direkt satış modeline uygun hale getirmek
- m² fiyatlı ürün hesaplama deneyimini sadeleştirmek
- Siparişten ödemeye ve kargoya kadar akışı netleştirmek
- Hukuki, teknik ve operasyonel eksikleri daha en baştan kapatmak
- Tek kişinin sürdürebileceği bir sistem kurmak

### Başarı kriterleri
- Kullanıcı ürün detayını anlayabiliyor mu?
- Kullanıcı fiyatı doğru görebiliyor mu?
- Sepet ve ödeme akışı hatasız mı?
- KDV ve kargo mantığı şeffaf mı?
- Mobilde alışveriş yapılabiliyor mu?
- Sipariş sonrası operasyon yönetilebilir mi?

---

## 2. Repo ve Dokümantasyon Yapısı

### Repo adı
- `woocommerce-store`

### Önerilen klasör yapısı

```text
woocommerce-store/
├── child-theme/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── fonts/
│   ├── woocommerce/
│   ├── parts/
│   ├── functions.php
│   ├── style.css
│   └── screenshot.png
├── docs/
│   ├── 01-brand-kit.md
│   ├── 02-site-architecture.md
│   ├── 03-product-model.md
│   ├── 04-pricing-logic.md
│   ├── 05-legal-pages.md
│   ├── 06-operations.md
│   ├── 07-seo-plan.md
│   ├── 08-testing-checklist.md
│   └── 09-launch-checklist.md
├── branding/
│   ├── logo/
│   ├── colors/
│   ├── typography/
│   ├── packaging/
│   └── product-templates/
├── automation/
│   └── n8n/
└── README.md
```

### Ana dokümanlar
- Brand kit
- Site mimarisi
- Ürün veri modeli
- Fiyatlandırma mantığı
- Hukuki sayfalar
- Operasyon akışı
- SEO planı
- Test checklist’i
- Lansman checklist’i

---

## 3. GitHub Projects Yönetim Sistemi

### Board kolonları
- Inbox
- Backlog
- Research
- Branding
- Design
- Development
- Content
- Legal
- Testing
- Ready
- Live
- Operations
- Maintenance

### Label sistemi
- `frontend`
- `backend`
- `woocommerce`
- `design`
- `branding`
- `seo`
- `content`
- `legal`
- `automation`
- `operations`
- `bug`
- `enhancement`
- `research`

### Milestone önerileri
- Brand Kit
- Foundation Setup
- WooCommerce Core
- Product System
- Frontend UX
- Legal Readiness
- Testing
- Launch
- Post-Launch Optimization

---

## 4. Brand Kit To-Do List

### Marka stratejisi
- [ ] Marka ismini netleştir
- [ ] Marka vaadini yaz
- [ ] Hedef müşteri profilini yaz
- [ ] Rakip marka dilini analiz et
- [ ] Marka tonu belirle: teknik / güvenli / dürüst / profesyonel

### Logo sistemi
- [ ] Primary logo
- [ ] Horizontal logo
- [ ] Icon logo
- [ ] Monochrome versiyon
- [ ] Favicon versiyonu
- [ ] Kullanım kuralları dokümanı

### Renk sistemi
- [ ] Primary renk seçimi
- [ ] Secondary renk seçimi
- [ ] Accent / CTA rengi
- [ ] Neutral gri tonları
- [ ] UI durum renkleri: success / warning / error / info

### Tipografi
- [ ] Heading fontu
- [ ] Body fontu
- [ ] Teknik veri fontu gerekiyorsa belirle
- [ ] Font size scale çıkar
- [ ] Desktop / mobile tipografi kuralları yaz

### Görsel dil
- [ ] Ürün fotoğraf standartları
- [ ] Uygulama görsel dili
- [ ] Montaj detayı görsel dili
- [ ] Social media görsel şablonu
- [ ] Ürün kartı görsel şablonu

### UI brand bileşenleri
- [ ] Primary button
- [ ] Secondary button
- [ ] Outline button
- [ ] Badge yapısı
- [ ] Feature card
- [ ] Trust badge
- [ ] Teknik tablo stili

### Kargo ve paket kimliği
- [ ] Teşekkür kartı
- [ ] Kutu içi bilgilendirme kartı
- [ ] Montaj notu kartı
- [ ] QR kod yönlendirmesi
- [ ] Kargo etiketi şablonu

---

## 5. Altyapı ve Foundation To-Do List

### Hosting ve domain
- [ ] Domain bağla
- [ ] SSL aktif et
- [ ] Hosting panel erişimlerini güvenli sakla
- [ ] PHP sürümünü 8.1 veya 8.2 yap
- [ ] Memory limit değerini kontrol et
- [ ] Upload limit değerini kontrol et
- [ ] Cron ve mail yapılandırmasını doğrula

### WordPress kurulumu
- [ ] Temiz WordPress kurulumu
- [ ] Yönetici kullanıcı oluştur
- [ ] Güçlü şifre ve 2FA ayarla
- [ ] Site başlığı ve sloganı belirle
- [ ] Kalıcı bağlantıları ayarla
- [ ] Varsayılan gereksiz eklentileri kaldır
- [ ] Varsayılan tema dışında gerekli parent theme’i kur

### Temel pluginler
- [ ] Cache eklentisi kur
- [ ] SEO eklentisi kur
- [ ] Backup eklentisi kur
- [ ] Security eklentisi kur
- [ ] SMTP eklentisi kur
- [ ] Image optimization eklentisi kur
- [ ] Cookie consent çözümünü kur

### Güvenlik
- [ ] Admin URL güvenliğini kontrol et
- [ ] Login deneme sınırı ayarla
- [ ] XML-RPC durumunu kontrol et
- [ ] Dosya izinlerini kontrol et
- [ ] Düzenli yedek planı oluştur
- [ ] Yedek geri yükleme testi yap

---

## 6. Parent Theme + Child Theme Geliştirme Planı

### Parent theme seçimi
- [ ] Astra / GeneratePress / Kadence arasında karar ver
- [ ] Parent theme kurulumu
- [ ] Performans ayarlarını minimum gereksinimle optimize et

### Child theme kurulumu
- [ ] `style.css` oluştur
- [ ] `functions.php` oluştur
- [ ] Child theme aktif et
- [ ] CSS enqueue et
- [ ] JS enqueue et
- [ ] Asset klasör yapısını oluştur

### Frontend mimarisi
- [ ] Header yapısını planla
- [ ] Footer yapısını planla
- [ ] Global spacing sistemi belirle
- [ ] Global renk sınıfları oluştur
- [ ] Global button sınıfları oluştur
- [ ] Form stil sistemi oluştur
- [ ] Mobil breakpoint mantığını yaz

### Kod standardı
- [ ] CSS naming convention belirle
- [ ] JS modül yapısı belirle
- [ ] Template dosya adlandırma standardı yaz
- [ ] Component mantığı oluştur

---

## 7. WooCommerce Temel Kurulum To-Do List

### Genel ayarlar
- [ ] Mağaza ülkesini ayarla
- [ ] Para birimini TRY yap
- [ ] KDV yapılandırmasını aç
- [ ] Ağırlık ve boyut birimlerini seç
- [ ] Satış yapılan bölgeyi tanımla
- [ ] Misafir ödeme kararını ver

### Ürün ayarları
- [ ] Ürün katalog görünümünü belirle
- [ ] Stok yönetimini aç/kapat kararını ver
- [ ] SKU kullanımını netleştir
- [ ] Ürün medya standartlarını belirle

### Vergi ayarları
- [ ] Vergi sınıflarını tanımla
- [ ] KDV oranını gir
- [ ] Fiyatların KDV dahil/haric mantığını belirle
- [ ] Sepet ve checkout’ta KDV gösterimini planla
- [ ] Faturada gösterilecek vergi dilini netleştir

### Kargo ayarları
- [ ] Türkiye kargo bölgesi oluştur
- [ ] Temel kargo yöntemi belirle
- [ ] Sabit / dinamik / koşullu kargo mantığını planla
- [ ] Kargo maliyet alanlarını dokümante et
- [ ] Kargo açıklama metinlerini hazırla

### Ödeme ayarları
- [ ] Sanal POS eklentisini seç
- [ ] Sanal POS kur
- [ ] Test modunda ödeme dene
- [ ] Havale/EFT ödeme yöntemini ekle
- [ ] Banka hesap bilgilerini gir
- [ ] Sipariş sonrası ödeme talimatı metnini yaz

### Mail ayarları
- [ ] SMTP yapılandır
- [ ] Sipariş maillerini test et
- [ ] Admin bildirim mailini ayarla
- [ ] Müşteri sipariş onay mailini özelleştir
- [ ] Havale siparişi mail metnini düzenle

---

## 8. Ürün Veri Modeli ve Katalog Tasarımı

### Katalog stratejisi
- [ ] Kategori ağacını kur
- [ ] Ürün tiplerini belirle
- [ ] Basit ürün / varyasyonlu ürün kullanımını planla
- [ ] Teknik filtre ihtiyaçlarını yaz

### Güvenlik ağı ürün veri modeli
- [ ] Birim fiyat (m²) alanı
- [ ] Min ölçü alanı
- [ ] Max ölçü alanı
- [ ] Halat tipi alanı
- [ ] Göz aralığı alanı
- [ ] Renk alanı
- [ ] Mukavemet bilgisi
- [ ] Uygulama alanı bilgisi
- [ ] Teslim süresi bilgisi

### Ürün içerik standardı
- [ ] Ürün başlığı şablonu oluştur
- [ ] Kısa açıklama şablonu oluştur
- [ ] Teknik özellik tablosu şablonu oluştur
- [ ] Kullanım alanları bloğu oluştur
- [ ] Paket içeriği bloğu oluştur
- [ ] Kargo bilgisi bloğu oluştur
- [ ] Uyarılar ve kullanım notları alanı oluştur

### Görsel yapısı
- [ ] Ana ürün görseli standardı
- [ ] Uygulama görseli standardı
- [ ] Detay çekim görseli standardı
- [ ] Teknik çizim / ölçü görseli standardı
- [ ] Alt text standardı

---

## 9. m² Fiyatlandırma Sistemi

### İş mantığı dokümantasyonu
- [ ] Alan hesaplama mantığını yaz
- [ ] Fiyat hesaplama mantığını yaz
- [ ] KDV ekleme mantığını yaz
- [ ] Kargo ekleme mantığını yaz
- [ ] Havale ve kart ödeme farkı varsa yaz

### Hesaplama formülü

```text
alan = genişlik × yükseklik
ürün_tutarı = alan × m2_birim_fiyat
kdv_tutarı = ürün_tutarı × KDV_oranı
toplam = ürün_tutarı + kdv_tutarı + kargo
```

### UI to-do
- [ ] Genişlik input alanı
- [ ] Yükseklik input alanı
- [ ] Alan sonucu gösterimi
- [ ] Birim fiyat gösterimi
- [ ] KDV tutarı gösterimi
- [ ] Kargo tutarı gösterimi
- [ ] Genel toplam gösterimi
- [ ] Hata durumları gösterimi
- [ ] Minimum ölçü uyarısı
- [ ] Maksimum ölçü uyarısı

### Teknik to-do
- [ ] Ürün fiyatını backend’den çek
- [ ] Data attribute veya localized script ile JS’e aktar
- [ ] Hesaplayıcı JS modülü yaz
- [ ] Form validasyonlarını yaz
- [ ] Sepete doğru veri aktarımını planla
- [ ] Sipariş detayında ölçü bilgisini sakla
- [ ] Admin sipariş ekranında ölçü bilgisini göster
- [ ] Mail içinde ölçü bilgisini göster

### Karar verilmesi gerekenler
- [ ] Ondalık ölçü kabul edilecek mi?
- [ ] Minimum sipariş alanı olacak mı?
- [ ] Kargo sabit mi, eşiklere göre mi değişecek?
- [ ] KDV ürün detayında ayrı mı yazacak, toplamda mı?

---

## 10. WooCommerce Template Override To-Do List

### Ana override dosyaları
- [ ] `single-product.php`
- [ ] `archive-product.php`
- [ ] `content-product.php`
- [ ] `single-product/title.php`
- [ ] `single-product/price.php`
- [ ] `single-product/short-description.php`
- [ ] `cart/cart.php`
- [ ] `checkout/form-checkout.php`

### Ürün sayfası görevleri
- [ ] Hero alanı oluştur
- [ ] Görsel galeriyi yeniden tasarla
- [ ] Ölçü hesaplayıcı alanını ekle
- [ ] Teknik özellikler sekmesini tasarla
- [ ] Güven badge alanını ekle
- [ ] Kargo ve teslimat bilgisi kutusu ekle
- [ ] İade / hijyen notu alanı ekle
- [ ] Sabit CTA alanı oluştur
- [ ] Mobil ürün sayfası optimizasyonu yap

### Arşiv ve kategori sayfası görevleri
- [ ] Ürün kartı tasarla
- [ ] Fiyat gösterim mantığını belirle
- [ ] m² fiyat etiketi gösterimini oluştur
- [ ] Hover durumlarını tasarla
- [ ] Mobil grid mantığını düzenle
- [ ] Filtreleme alanını planla

### Sepet ve checkout görevleri
- [ ] Sepette ölçü bilgisini göster
- [ ] KDV görünümünü netleştir
- [ ] Kargo kalemini şeffaf göster
- [ ] Ödeme yöntemleri açıklamasını ekle
- [ ] Havale bilgi kutusu ekle
- [ ] Sözleşme onay alanlarını test et

---

## 11. Frontend / UX / UI Geliştirmeleri

### Ana sayfa
- [ ] Hero section
- [ ] Güven ve kalite mesajı
- [ ] Kategori kartları
- [ ] Neden bizi tercih etmelisiniz bölümü
- [ ] Sipariş süreci anlatımı
- [ ] Referans veya uygulama görselleri
- [ ] Kargo ve ödeme açıklaması
- [ ] SSS bölümü
- [ ] Footer güven alanı

### Header
- [ ] Logo yerleşimi
- [ ] Ana menü
- [ ] Mobil menü
- [ ] Arama alanı
- [ ] Sepet simgesi
- [ ] WhatsApp / iletişim butonu gerekiyorsa ekle

### Footer
- [ ] Kurumsal linkler
- [ ] Hukuki sayfalar
- [ ] İletişim bilgileri
- [ ] Sosyal medya linkleri
- [ ] Kargo / ödeme bilgi kısa notları

### Ürün sayfası UX
- [ ] Başlık alanı
- [ ] Fiyat alanı
- [ ] Ölçü hesaplayıcı
- [ ] Teknik tablo
- [ ] Kargo notu
- [ ] Teslimat notu
- [ ] İade/hijyen notu
- [ ] Sepete ekle alanı
- [ ] Güven rozeti alanı

### Mobil UX
- [ ] Sticky add to cart
- [ ] Hesaplayıcı mobil uyumu
- [ ] Font okunabilirliği
- [ ] Touch target optimizasyonu
- [ ] Görsel oran optimizasyonu

---

## 12. JavaScript Geliştirme To-Do List

### Temel JS modülleri
- [ ] m² hesaplayıcı modülü
- [ ] KDV hesap modülü
- [ ] Kargo hesap modülü
- [ ] Form validasyon modülü
- [ ] UI state yönetimi
- [ ] Hata mesajı render modülü

### Event mantığı
- [ ] `input` event yönetimi
- [ ] `change` event yönetimi
- [ ] Sepete ekle öncesi kontrol
- [ ] Mobil sticky CTA etkileşimi

### Veri akışı
- [ ] PHP’den ürün verisini JS’e aktarma
- [ ] `data-*` attribute stratejisi
- [ ] Gerekiyorsa `wp_localize_script` kullanımı
- [ ] Siparişe custom meta veri aktarımı

### Geliştirici notları
- [ ] Vanilla JS ile ilerle
- [ ] Gereksiz framework kullanma
- [ ] Modüler ve okunabilir dosya yapısı kur
- [ ] Tek dosyada devasa script yazma

---

## 13. İçerik ve SEO To-Do List

### Ana sayfalar
- [ ] Ana sayfa metinleri
- [ ] Hakkımızda sayfası
- [ ] İletişim sayfası
- [ ] SSS sayfası
- [ ] Kategori sayfası açıklamaları

### Ürün içerikleri
- [ ] Ürün başlıklarını SEO uyumlu yaz
- [ ] Kısa açıklamaları oluştur
- [ ] Teknik tabloyu doldur
- [ ] Kullanım alanlarını yaz
- [ ] Kargo bilgisini yaz
- [ ] Teslimat süresini yaz
- [ ] Uyarılar bölümünü yaz

### SEO temelleri
- [ ] Title template oluştur
- [ ] Meta description template oluştur
- [ ] URL slug standardı belirle
- [ ] Image alt text standardı yaz
- [ ] Sitemap kontrolü yap
- [ ] Search Console bağla
- [ ] Robots.txt kontrol et

### Teknik SEO
- [ ] Schema ihtiyaçlarını değerlendir
- [ ] Breadcrumb yapısını kontrol et
- [ ] Canonical mantığını kontrol et
- [ ] 404 sayfasını tasarla
- [ ] Redirect stratejisini yaz

---

## 14. Hukuki Sayfalar ve Uyum To-Do List

### Zorunlu / gerekli sayfalar
- [ ] Gizlilik Politikası
- [ ] KVKK Aydınlatma Metni
- [ ] Mesafeli Satış Sözleşmesi
- [ ] İade ve İptal Politikası
- [ ] Ödeme ve Teslimat
- [ ] Çerez Politikası

### İçerik özel maddeleri
- [ ] Paket açılırsa ve hijyen kurallarına uymazsa iade kabul edilmez maddesini ekle
- [ ] Müşteri yanlış siparişlerinden kendisi sorumludur maddesini ekle
- [ ] Ölçü ve varyasyon sorumluluğunu net yaz
- [ ] Kargo ücretinin nasıl hesaplandığını yaz
- [ ] KDV gösterim mantığını açık yaz
- [ ] Havale sipariş prosedürünü yaz

### Checkout uyumu
- [ ] Mesafeli satış sözleşmesini checkout’a bağla
- [ ] Gizlilik / KVKK onay akışını doğrula
- [ ] Çerez onay banner’ını kontrol et
- [ ] Satıcı iletişim ve firma bilgilerini görünür yap

---

## 15. Operasyon ve Sipariş Süreci To-Do List

### Sipariş akışı
- [ ] Sipariş geldiğinde izlenecek adımları yaz
- [ ] Havale sipariş doğrulama akışını yaz
- [ ] Ödeme alındı sonrası hazırlık akışını yaz
- [ ] Kargo çıkış akışını yaz
- [ ] İptal / iade talebi akışını yaz

### Operasyon paneli
- [ ] Sipariş durumu tablosu oluştur
- [ ] Kargo takip kolonları oluştur
- [ ] Havale kontrol alanı oluştur
- [ ] Müşteri notları alanı oluştur
- [ ] İade kayıt alanı oluştur

### Mail ve mesaj şablonları
- [ ] Sipariş alındı maili
- [ ] Ödeme bekleniyor maili
- [ ] Havale bilgisi maili
- [ ] Sipariş hazırlanıyor maili
- [ ] Kargoya verildi maili
- [ ] İade talebi cevabı

### Kargo operasyonu
- [ ] Paketleme standartları belirle
- [ ] Kargo firması süreçlerini yaz
- [ ] Kargo ücret mantığını yaz
- [ ] Kargo takip süreci oluştur
- [ ] Hasarlı ürün prosedürü yaz

---

## 16. Analitik ve Ölçümleme To-Do List

### Kurulum
- [ ] Google Analytics 4 kur
- [ ] Google Search Console kur
- [ ] Dönüşüm event’lerini tanımla
- [ ] E-ticaret event’lerini test et

### Takip edilecek metrikler
- [ ] Ürün görüntüleme
- [ ] Sepete ekleme
- [ ] Checkout başlatma
- [ ] Sipariş tamamlama
- [ ] Hatalı ödeme
- [ ] En çok görüntülenen ürünler
- [ ] En çok terk edilen sayfalar

### Raporlama
- [ ] Haftalık satış raporu şablonu
- [ ] Haftalık trafik raporu şablonu
- [ ] En çok satan ürün raporu
- [ ] Kargo maliyeti takibi

---

## 17. n8n / Otomasyon Fikirleri

> Şu aşamada zorunlu değil ama sonraki faz için değerlidir.

### İlk otomasyon adayları
- [ ] Yeni sipariş → Telegram bildirimi
- [ ] Yeni sipariş → Google Sheets kayıt
- [ ] Havale siparişi → manuel kontrol uyarısı
- [ ] Günlük satış özeti → mail / Telegram
- [ ] İade talebi → görev oluşturma

### Otomasyon öncesi gereksinimler
- [ ] Sipariş durumlarını standartlaştır
- [ ] Webhook mantığını dokümante et
- [ ] Hangi verilerin paylaşılacağını belirle
- [ ] Gizlilik ve erişim güvenliğini kontrol et

---

## 18. Testing Checklist

### Teknik testler
- [ ] Site açılış hızı
- [ ] 404 testi
- [ ] Mobil responsive test
- [ ] Menü testleri
- [ ] Arama testi
- [ ] Görsel optimizasyon testi

### WooCommerce testleri
- [ ] Sepete ürün ekleme
- [ ] m² hesaplama doğruluğu
- [ ] KDV hesaplama doğruluğu
- [ ] Kargo hesaplama doğruluğu
- [ ] Sanal POS test ödemesi
- [ ] Havale siparişi testi
- [ ] Sipariş mailleri testi
- [ ] Admin sipariş görünümü testi

### İçerik testleri
- [ ] Yazım kontrolü
- [ ] Hukuki sayfa kontrolü
- [ ] İletişim bilgileri doğruluğu
- [ ] Ürün görselleri kontrolü
- [ ] Teknik tablo doğruluğu

### UX testleri
- [ ] İlk kez giren kullanıcı ürünü anlayabiliyor mu?
- [ ] Ölçü girme alanı net mi?
- [ ] Toplam fiyat görünür mü?
- [ ] Mobilde hesaplayıcı kullanılabiliyor mu?
- [ ] Checkout’ta kafa karışıklığı oluşuyor mu?

---

## 19. Launch Checklist

### Yayın öncesi
- [ ] Cache ve performans ayarlarını son kez kontrol et
- [ ] SSL ve mixed content kontrolü yap
- [ ] Noindex durumunu kapat
- [ ] Sitemap gönder
- [ ] Analytics çalışıyor mu kontrol et
- [ ] Mail akışını son kez test et
- [ ] Checkout onay kutularını test et
- [ ] Tüm hukuki sayfaların linklerini kontrol et

### İçerik hazır mı?
- [ ] Ana sayfa tamam
- [ ] Kategori sayfaları tamam
- [ ] Ürün sayfaları tamam
- [ ] Hakkımızda tamam
- [ ] İletişim tamam
- [ ] Footer tamam

### Operasyon hazır mı?
- [ ] Sipariş geldiğinde kim ne yapacak belli mi?
- [ ] Kargo hazırlık süreci net mi?
- [ ] Havale kontrol süreci net mi?
- [ ] Müşteri destek metinleri hazır mı?

---

## 20. Post-Launch İyileştirme Listesi

### İlk 30 gün
- [ ] Hangi ürünler en çok görüntüleniyor bak
- [ ] Hangi noktada kullanıcı düşüyor bak
- [ ] Mobilde en çok hangi sayfalar ziyaret ediliyor bak
- [ ] Kargo ve ödeme soruları topla
- [ ] Ürün sayfası dönüşümünü gözlemle

### İyileştirme adayları
- [ ] Ürün karşılaştırma
- [ ] SSS genişletme
- [ ] Kargo FAQ alanı
- [ ] Müşteri yorumları
- [ ] Gelişmiş filtreleme
- [ ] Kampanya alanı
- [ ] Bundle / çoklu ürün satış kurgusu

---

## 21. Önceliklendirme Planı

### Faz 1 — Foundation
- Brand kit
- Hosting / WordPress / güvenlik
- Parent + child theme
- WooCommerce temel ayarlar
- Hukuki sayfalar taslağı

### Faz 2 — Core Commerce
- Ürün veri modeli
- m² fiyat mantığı
- Ürün sayfası tasarımı
- Sepet / checkout akışı
- Ödeme sistemleri

### Faz 3 — Content + UX
- Kategori içerikleri
- Ürün içerikleri
- Mobil UX
- Güven alanları
- Kargo ve teslimat açıklamaları

### Faz 4 — Testing + Launch
- Test siparişleri
- SEO kontrolleri
- Analitik kurulum
- Lansman kontrol listesi

### Faz 5 — Optimization
- Hız optimizasyonu
- Dönüşüm iyileştirme
- Otomasyon
- Raporlama

---

## 22. En Kritik Riskler

- m² fiyat mantığının kullanıcıya net anlatılamaması
- KDV ve kargo toplamının şeffaf görünmemesi
- Sepette ölçü bilgisinin kaybolması
- Hukuki sayfaların eksik kalması
- Mobil ürün sayfasında hesaplayıcının kötü çalışması
- Havale siparişi süreçlerinin karışması
- Parent theme yerine doğrudan dosya düzenlenmesi

---

## 23. En Kritik İlk 15 Görev

1. [ ] Brand kit ana kararlarını ver  
2. [ ] Parent theme seç  
3. [ ] Child theme kur  
4. [ ] CSS ve JS asset yapısını oluştur  
5. [ ] WooCommerce temel ayarlarını tamamla  
6. [ ] Vergi/KDV mantığını netleştir  
7. [ ] Kargo mantığını yaz  
8. [ ] Sanal POS test kurulumunu yap  
9. [ ] Havale yöntemini ekle  
10. [ ] Ürün veri modelini dokümante et  
11. [ ] m² hesaplama mantığını yaz  
12. [ ] Ürün sayfası wireframe oluştur  
13. [ ] Hukuki sayfaların ilk sürümünü hazırla  
14. [ ] Test siparişi senaryosunu çıkar  
15. [ ] GitHub Projects board üzerinde issue’ları aç  

---

## 24. Çalışma Kuralı

Bu projede temel prensip şudur:

- Önce sistemi yaz
- Sonra tasarımı bağla
- Sonra içeriği doldur
- Sonra test et
- Sonra yayına al

Şunları erken yapma:
- Gereksiz eklenti ekleme
- Builder ile yapıyı şişirme
- Checkout’u baştan icat etmeye çalışma
- Parent theme dosyalarına direkt müdahale etme

Şunları erken yap:
- Veri modeli
- Fiyat mantığı
- Hukuki sayfalar
- Ürün sayfası UX
- Sipariş operasyon akışı

---

## 25. Son Not

Bu proje sadece bir site kurma işi değil. Bu proje aslında:
- ürün sistemi kurma,
- fiyat motoru kurma,
- güven inşa etme,
- sipariş operasyonunu sadeleştirme

işidir.

Doğru ilerleme sırası:

```text
Brand Kit
→ Foundation
→ WooCommerce Core
→ Product Model
→ Pricing Logic
→ Product Page UX
→ Legal
→ Testing
→ Launch
→ Optimization
```

Bu dosya ana yol haritasıdır. Bundan sonra her ana başlık ayrı `docs/*.md` dosyasına bölünerek detaylandırılabilir.
