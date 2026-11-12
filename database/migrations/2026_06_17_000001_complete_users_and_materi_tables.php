<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Lengkapi tabel users tanpa merusak data yang ada
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'nomor_induk')) {
                    $table->string('nomor_induk', 100)->nullable()->unique()->after('name');
                }
                if (!Schema::hasColumn('users', 'email')) {
                    $table->string('email')->nullable()->unique()->after('nomor_induk');
                }
                if (!Schema::hasColumn('users', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->tinyInteger('role')->default(0)->after('password');
                }
                if (!Schema::hasColumn('users', 'foto')) {
                    $table->string('foto')->nullable()->after('role');
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
            });

            // Pastikan panjang kolom name & password mencukupi untuk hash bcrypt
            DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL');
        }

        // 2. Buat atau lengkapi tabel materi (tanpa kolom XP)
        if (!Schema::hasTable('materi')) {
            Schema::create('materi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_id')->nullable()->index();
                $table->string('judul')->nullable();
                $table->string('judul_materi');
                $table->string('bab', 100)->default('Bab 1');
                $table->string('sub_bab', 100)->default('Sub 1');
                $table->string('kelas', 50)->nullable();
                $table->text('deskripsi')->nullable();
                $table->longText('konten_materi')->nullable();
                $table->boolean('has_video')->default(false);
                $table->string('video_url', 500)->nullable();
                $table->string('tipe_studi_kasus', 100)->nullable();
                $table->longText('soal_studi_kasus')->nullable();
                $table->unsignedInteger('urutan')->default(1)->index();
                $table->timestamps();
            });
        } else {
            Schema::table('materi', function (Blueprint $table) {
                if (!Schema::hasColumn('materi', 'judul_materi')) {
                    $table->string('judul_materi')->nullable()->after('id');
                }
                if (!Schema::hasColumn('materi', 'bab')) {
                    $table->string('bab', 100)->default('Bab 1')->after('judul_materi');
                }
                if (!Schema::hasColumn('materi', 'sub_bab')) {
                    $table->string('sub_bab', 100)->default('Sub 1')->after('bab');
                }
                if (!Schema::hasColumn('materi', 'konten_materi')) {
                    $table->longText('konten_materi')->nullable()->after('sub_bab');
                }
                if (!Schema::hasColumn('materi', 'has_video')) {
                    $table->boolean('has_video')->default(false)->after('konten_materi');
                }
                if (!Schema::hasColumn('materi', 'video_url')) {
                    $table->string('video_url', 500)->nullable()->after('has_video');
                }
                if (!Schema::hasColumn('materi', 'tipe_studi_kasus')) {
                    $table->string('tipe_studi_kasus', 100)->nullable()->after('video_url');
                }
                if (!Schema::hasColumn('materi', 'soal_studi_kasus')) {
                    $table->longText('soal_studi_kasus')->nullable()->after('tipe_studi_kasus');
                }
                if (!Schema::hasColumn('materi', 'urutan')) {
                    $table->unsignedInteger('urutan')->default(1)->index()->after('soal_studi_kasus');
                }
                if (Schema::hasColumn('materi', 'total_xp')) {
                    $table->dropColumn('total_xp');
                }
            });

            DB::statement('ALTER TABLE `materi` MODIFY `judul` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `materi` MODIFY `guru_id` INT NULL');
        }

        // 3. Pastikan tabel siswa_materi_progress tersedia
        if (!Schema::hasTable('siswa_materi_progress')) {
            Schema::create('siswa_materi_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('materi_id')->index();
                $table->integer('progress')->default(0);
                $table->enum('status', ['belum', 'aktif', 'selesai'])->default('belum');
                $table->timestamps();
            });
        }

        // 4. Seed data awal materi sesuai alur pembelajaran jika belum ada materi dengan tipe_studi_kasus
        $hasValidMateri = DB::table('materi')->whereNotNull('tipe_studi_kasus')->exists();
        if (!$hasValidMateri) {
            DB::table('materi')->delete();

            $guruId = DB::table('users')->where('role', 1)->value('id') ?? 1;
            $now = now();

            DB::table('materi')->insert([
                [
                    'guru_id' => $guruId,
                    'judul' => 'Pengenalan HTML',
                    'judul_materi' => 'Pengenalan HTML',
                    'bab' => 'Bab 1',
                    'sub_bab' => 'Sub 1',
                    'kelas' => 'XI RPL',
                    'deskripsi' => 'HTML (HyperText Markup Language) adalah bahasa markah standar untuk membuat dan menyusun halaman web.',
                    'konten_materi' => 'HTML (HyperText Markup Language) adalah bahasa markah standar untuk membuat dan menyusun halaman web. Pada materi ini, siswa mempelajari fungsi dasar HTML, cara kerja browser dalam merender dokumen HTML, serta elemen-elemen dasar pembentuk halaman web.',
                    'has_video' => 1,
                    'video_url' => 'https://www.youtube.com/watch?v=NBZ9Ro6UKV8',
                    'tipe_studi_kasus' => 'Esai singkat',
                    'soal_studi_kasus' => 'Jelaskan dengan bahasamu sendiri mengapa HTML disebut sebagai kerangka dasar sebuah halaman web, dan sebutkan minimal 3 contoh penggunaan elemen HTML pada halaman website sekolah!',
                    'urutan' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'guru_id' => $guruId,
                    'judul' => 'Tag, Atribut, dan Struktur Dokumen',
                    'judul_materi' => 'Tag, Atribut, dan Struktur Dokumen',
                    'bab' => 'Bab 1',
                    'sub_bab' => 'Sub 2',
                    'kelas' => 'XI RPL',
                    'deskripsi' => 'Mempelajari struktur wajib dokumen HTML (DOCTYPE, html, head, body) beserta penggunaan tag dan atribut.',
                    'konten_materi' => 'Dokumen HTML memiliki struktur wajib yang terdiri dari deklarasi <!DOCTYPE html>, elemen <html>, <head>, dan <body>. Setiap elemen dibangun menggunakan tag pembuka dan penutup serta dapat dilengkapi atribut seperti id, class, href, dan src.',
                    'has_video' => 0,
                    'video_url' => null,
                    'tipe_studi_kasus' => 'Pilihan ganda',
                    'soal_studi_kasus' => 'Sebuah halaman web tidak menampilkan judul pada tab browser dan gambar logo tidak muncul. Manakah kombinasi tag di dalam <head> dan atribut pada tag <img> yang tepat untuk memperbaiki masalah tersebut?',
                    'urutan' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'guru_id' => $guruId,
                    'judul' => 'Dasar CSS Selector',
                    'judul_materi' => 'Dasar CSS Selector',
                    'bab' => 'Bab 2',
                    'sub_bab' => 'Sub 1',
                    'kelas' => 'XI RPL',
                    'deskripsi' => 'Mempelajari penggunaan element selector, class selector, dan ID selector untuk mengatur tampilan halaman web.',
                    'konten_materi' => 'CSS (Cascading Style Sheets) digunakan untuk mengatur tampilan dan tata letak elemen HTML. Selector pada CSS berfungsi memilih elemen HTML yang ingin diberi gaya, mulai dari element selector, class selector (.nama-class), hingga ID selector (#nama-id).',
                    'has_video' => 1,
                    'video_url' => 'https://www.youtube.com/watch?v=CleFk3BZB3g',
                    'tipe_studi_kasus' => 'Kode program',
                    'soal_studi_kasus' => 'Buatlah kode CSS menggunakan class selector .card-materi dan id selector #header-utama untuk memberikan warna latar belakang, sudut membulat (border-radius), serta warna teks yang kontras!',
                    'urutan' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('materi')) {
            Schema::table('materi', function (Blueprint $table) {
                $cols = ['judul_materi', 'bab', 'sub_bab', 'konten_materi', 'has_video', 'video_url', 'tipe_studi_kasus', 'soal_studi_kasus', 'urutan'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('materi', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
