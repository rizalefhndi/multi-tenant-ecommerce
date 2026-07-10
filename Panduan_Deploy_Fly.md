# Panduan Lengkap Deploy Laravel Multi-Tenant ke Fly.io (Windows)

Dokumen ini berisi rangkuman langkah-langkah *deployment* dari awal hingga akhir agar Anda mudah mengulangnya nanti di komputer rumah.

---

## 1. Persiapan Awal (Instalasi Fly.io di Windows)
Buka aplikasi **PowerShell** di Windows Anda, lalu jalankan perintah instalasi berikut:
```powershell
iwr https://fly.io/install.ps1 -useb | iex
```
*Catatan: Pastikan Anda menambahkan path Fly.io ke Environment Variables Windows jika command `flyctl` tidak ditemukan (biasanya di `C:\Users\NAMA_USER\.fly\bin`).*

Setelah terinstal, lakukan proses *login*:
```powershell
C:\Users\admin\.fly\bin\flyctl.exe auth login
```
*Browser akan terbuka, silakan klik tombol konfirmasi login.*

---

## 2. Inisiasi Aplikasi (Jangan Langsung Deploy)
Buka PowerShell, pastikan posisi Anda sudah berada di dalam folder project Laravel Anda (misal: `cd E:\wgs-zal\...`).

Jalankan perintah ini untuk membuat konfigurasi awal:
```powershell
C:\Users\admin\.fly\bin\flyctl.exe launch
```
**PENTING SAAT MUNCUL PERTANYAAN DI TERMINAL:**
- `Do you want to tweak these settings before proceeding?` -> Ketik **`y`** lalu Enter.
- Browser akan terbuka. Di halaman tersebut:
  1. Ubah bagian **Database (Postgres)** dari *Basic* menjadi **Development** (agar gratis).
  2. Ubah bagian **Redis** menjadi **None**.
  3. Klik **Confirm/Deploy** di paling bawah.
- Tunggu hingga terminal selesai memproses dan memunculkan notifikasi sukses membuat file `fly.toml`.

---

## 3. Optimasi Konfigurasi & Kodingan (Mencegah Error)

**A. Batasi Jumlah Server (Hemat Kuota)**
Buka file `fly.toml` yang baru terbuat. Cari bagian `[http_service]` dan ubah `min_machines_running` menjadi `0`:
```toml
[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = true
  auto_start_machines = true
  min_machines_running = 0
  processes = ["app"]
```

**B. Cegah Error File Cache Lokal (PailServiceProvider Error)**
Buka file `.dockerignore`, dan tambahkan baris ini di paling bawah:
```text
/bootstrap/cache/*.php
```

**C. Cegah Error Layar Blank Putih (Mixed Content HTTPS)**
Buka file `app/Providers/AppServiceProvider.php`, lalu tambahkan kode ini di dalam fungsi `boot()`:
```php
public function boot(): void
{
    \Illuminate\Support\Facades\Vite::prefetch(concurrency: 3);
    
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

---

## 4. Inject Rahasia (Database & Envrionment)
Gunakan perintah ini untuk menanamkan kredensial database Supabase ke dalam server Fly.io. *(Jangan ada enter di tengah-tengah perintah, jadikan 1 baris panjang!)*

```powershell
C:\Users\admin\.fly\bin\flyctl.exe secrets set DATABASE_URL="postgresql://postgres:[PASSWORD_SUPABASE]@db.lmjjogpqyosaqlvmqxby.supabase.co:5432/postgres" DB_CONNECTION="pgsql" APP_KEY="base64:jILWLlPNr84x0iU4Frp4VcT9skp3lJfKOLqoHEoOI4U=" APP_ENV="production" APP_DEBUG="false" DB_URL="postgresql://postgres:[PASSWORD_SUPABASE]@db.lmjjogpqyosaqlvmqxby.supabase.co:5432/postgres" CENTRAL_DOMAIN="nama-aplikasi-anda.fly.dev" APP_URL="https://nama-aplikasi-anda.fly.dev"
```
*(Ubah nilai URL dan DOMAIN di atas sesuai dengan konfigurasi Supabase dan nama aplikasi Fly Anda).*

---

## 5. Saatnya Deploy!
Setelah semua persiapan beres, eksekusi perintah utama untuk mengunggah kodingan Anda ke server:
```powershell
C:\Users\admin\.fly\bin\flyctl.exe deploy
```
*(Tunggu sekitar 3-5 menit hingga proses selesai 100% dan muncul tulisan Machine Started).*

---

## 6. Migrasi & Seed Database
Langkah terakhir adalah membuat tabel-tabel di Supabase agar website bisa diakses tanpa error 500. Jalankan dua perintah ini berurutan:

```powershell
C:\Users\admin\.fly\bin\flyctl.exe ssh console -C "php artisan migrate --force"
```
```powershell
C:\Users\admin\.fly\bin\flyctl.exe ssh console -C "php artisan db:seed --force"
```
*(Abaikan jika muncul pesan "Error: The handle is invalid" di akhir proses, itu hanya bug tampilan terminal PowerShell di Windows).*

---

🎉 **SELESAI!**
Website Laravel Multi-Tenant Anda sudah berhasil online sepenuhnya. Anda bisa mengaksesnya di `https://nama-aplikasi-anda.fly.dev`.



C:\Users\admin\.fly\bin\flyctl.exe auth login       
C:\Users\admin\.fly\bin\flyctl.exe deploy                             