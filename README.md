# Digital School Payment System (Midtrans Integration)

Sistem pembayaran sekolah berbasis web ini mengintegrasikan Midtrans Payment Gateway untuk mengelola seluruh tagihan sekolah dan menampilkan data pembayaran siswa secara real-time.

## Persyaratan Sistem (Prerequisites)

Pastikan lingkungan Anda memenuhi persyaratan berikut sebelum instalasi:

* PHP versi [e.g., 8.0 atau lebih baru]
* Composer
* Database MySQL
* Midtrans Account [e.g., MySQL / PostgreSQL]

## Teknologi Frontend

Proyek ini dikembangkan menggunakan:

* **Bootstrap 5 (via Hope UI Template):** Digunakan untuk desain responsive dan komponen UI admin.
* **Midtrans Snap.js:** Digunakan untuk memproses pembayaran secara pop-up di sisi klien.
* **JavaScript Dasar & Fetch API:** Digunakan untuk interaksi client-side (seperti Chatbot) dan komunikasi AJAX.

## Instalasi Proyek

Ikuti langkah-langkah ini untuk menjalankan proyek secara lokal:

1.  **Clone Repositori:**
    ```bash
    git clone [https://github.com/HaniPutriPratiwi0111/digital-school-payment-midtrans.git](https://github.com/HaniPutriPratiwi0111/digital-school-payment-midtrans.git)
    cd digital-school-payment-midtrans
    ```

2.  **Instal Dependensi Composer:**
    ```bash
    composer install
    ```

3.  **Pengaturan Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Konfigurasi Database & Midtrans:**
    * Edit berkas `.env` dan masukkan detail database Anda.
    * Masukkan kunci API Midtrans (Server Key & Client Key) ke dalam berkas `.env`.

5.  **Migrasi Database:**
    ```bash
    php artisan migrate --seed
    ```

6.  **Jalankan Aplikasi:**
    ```bash
    php artisan serve
    ```

## Lisensi

Proyek ini dilisensikan di bawah **Lisensi MIT (MIT License)**. Lisensi ini memberikan izin yang sangat luas untuk menggunakan, memodifikasi, dan mendistribusikan kode. Lihat berkas `LICENSE` untuk detail selengkapnya.
