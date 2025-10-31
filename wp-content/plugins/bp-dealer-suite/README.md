# BusinessPlus Dealer Suite

BusinessPlus Dealer Suite, WooCommerce için bayi/wholesale yönetimi, dinamik fiyatlandırma, bonus katmanları ve kayıp koruması sunan modern bir eklentidir.

## Kurulum

1. `wp-content/plugins/` dizinine kopyalayın.
2. WordPress yönetim panelinden **BusinessPlus Dealer Suite** eklentisini etkinleştirin.
3. WooCommerce > Ayarlar > Bayi Suite sekmesinden varsayılan ayarları yapılandırın.

## Özellikler

- Bayi seviyeleri ve özel indirim oranları
- Bonus katmanları ve Terra Wallet entegrasyonu
- Zarar koruma politikaları ve otomatik indirim kırpma
- REST API uçları (`/wp-json/bp-dealer/v1/`)
- WP-CLI komutları (`wp bp-dealer ...`)
- WooCommerce sepet ve ürün fiyatında dinamik indirimler

## WP-CLI Komutları

- `wp bp-dealer import-levels` — seviye verilerini içe aktar
- `wp bp-dealer import-bonus` — bonus katmanlarını içe aktar
- `wp bp-dealer rebuild-stats` — istatistikleri yeniden oluştur
- `wp bp-dealer seed-test-data` — örnek test verisi yükle

## Testler

PHPUnit testlerini çalıştırmak için:

```bash
vendor/bin/phpunit --testsuite bp-dealer-suite
```

## Güvenlik

- Yetki ve nonce kontrolleri
- REST API için capability doğrulaması
- WooCommerce fiyat hesaplamasında zarar koruması

## Lisan

GPL-2.0-or-later
