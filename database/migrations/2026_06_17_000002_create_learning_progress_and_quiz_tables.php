<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lengkapi tabel materi dengan opsi pilihan ganda untuk studi kasus (opsional)
        if (Schema::hasTable('materi')) {
            Schema::table('materi', function (Blueprint $table) {
                if (!Schema::hasColumn('materi', 'opsi_a')) {
                    $table->string('opsi_a', 500)->nullable()->after('soal_studi_kasus');
                }
                if (!Schema::hasColumn('materi', 'opsi_b')) {
                    $table->string('opsi_b', 500)->nullable()->after('opsi_a');
                }
                if (!Schema::hasColumn('materi', 'opsi_c')) {
                    $table->string('opsi_c', 500)->nullable()->after('opsi_b');
                }
                if (!Schema::hasColumn('materi', 'opsi_d')) {
                    $table->string('opsi_d', 500)->nullable()->after('opsi_c');
                }
                if (!Schema::hasColumn('materi', 'kunci_studi_kasus')) {
                    $table->string('kunci_studi_kasus', 10)->nullable()->after('opsi_d');
                }
            });

            // Isi opsi pilihan ganda untuk materi urutan 2 jika masih kosong
            DB::table('materi')
                ->where('tipe_studi_kasus', 'Pilihan ganda')
                ->whereNull('opsi_a')
                ->update([
                    'opsi_a' => 'Menggunakan tag <title> di dalam <head> dan atribut src="..." serta alt="..." pada tag <img>',
                    'opsi_b' => 'Menggunakan tag <header> di dalam <body> dan atribut href="..." pada tag <img>',
                    'opsi_c' => 'Menggunakan tag <meta> di luar <html> dan atribut link="..." pada tag <img>',
                    'opsi_d' => 'Menggunakan tag <h1> di dalam <head> dan atribut url="..." pada tag <img>',
                    'kunci_studi_kasus' => 'a',
                ]);
        }

        // 2. Lengkapi tabel siswa_materi_progress untuk menyimpan jawaban studi kasus & status
        if (!Schema::hasTable('siswa_materi_progress')) {
            Schema::create('siswa_materi_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('materi_id')->index();
                $table->integer('progress')->default(0);
                $table->string('status', 30)->default('terbuka');
                $table->boolean('baca_selesai')->default(false);
                $table->longText('jawaban_studi_kasus')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('siswa_materi_progress', function (Blueprint $table) {
                if (!Schema::hasColumn('siswa_materi_progress', 'baca_selesai')) {
                    $table->boolean('baca_selesai')->default(false)->after('status');
                }
                if (!Schema::hasColumn('siswa_materi_progress', 'jawaban_studi_kasus')) {
                    $table->longText('jawaban_studi_kasus')->nullable()->after('baca_selesai');
                }
                if (!Schema::hasColumn('siswa_materi_progress', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('jawaban_studi_kasus');
                }
            });

            DB::statement("ALTER TABLE `siswa_materi_progress` MODIFY `status` VARCHAR(30) DEFAULT 'terbuka'");
        }

        // 3. Buat tabel quizzes
        if (!Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_id')->nullable()->index();
                $table->unsignedBigInteger('materi_id')->nullable()->index();
                $table->string('judul_quiz');
                $table->text('deskripsi')->nullable();
                $table->unsignedInteger('durasi_menit')->default(15);
                $table->unsignedInteger('kkm')->default(70);
                $table->timestamps();
            });
        }

        // 4. Buat tabel quiz_questions
        if (!Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quiz_id')->index();
                $table->text('pertanyaan');
                $table->text('opsi_a');
                $table->text('opsi_b');
                $table->text('opsi_c');
                $table->text('opsi_d');
                $table->string('jawaban_benar', 5)->default('a');
                $table->unsignedInteger('bobot_nilai')->default(25);
                $table->unsignedInteger('urutan')->default(1);
                $table->timestamps();
            });
        }

        // 5. Buat tabel quiz_attempts
        if (!Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('quiz_id')->index();
                $table->unsignedInteger('skor')->default(0);
                $table->unsignedInteger('jawaban_benar')->default(0);
                $table->unsignedInteger('jawaban_salah')->default(0);
                $table->unsignedInteger('total_soal')->default(0);
                $table->longText('detail_jawaban')->nullable();
                $table->unsignedInteger('durasi_detik')->nullable();
                $table->timestamps();
            });
        }

        // 6. Buat tabel riwayat_belajar
        if (!Schema::hasTable('riwayat_belajar')) {
            Schema::create('riwayat_belajar', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('tipe_aktivitas', 50); // materi, studi_kasus, quiz
                $table->string('judul_aktivitas');
                $table->text('keterangan')->nullable();
                $table->integer('skor')->nullable();
                $table->unsignedBigInteger('referensi_id')->nullable();
                $table->timestamps();
            });
        }

        // 7. Seed paket Quiz awal dari Guru jika tabel quizzes masih kosong
        if (DB::table('quizzes')->count() === 0) {
            $guruId = DB::table('users')->where('role', 1)->value('id') ?? 1;
            $now = now();

            $quiz1Id = DB::table('quizzes')->insertGetId([
                'guru_id' => $guruId,
                'judul_quiz' => 'Quiz Evaluasi Bab 1: Fondasi & Struktur HTML',
                'deskripsi' => 'Uji pemahamanmu mengenai konsep dasar HTML, elemen, tag, atribut, dan struktur dokumen web.',
                'durasi_menit' => 10,
                'kkm' => 70,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('quiz_questions')->insert([
                [
                    'quiz_id' => $quiz1Id,
                    'pertanyaan' => 'Deklarasi apakah yang wajib ditulis pada baris paling pertama dokumen HTML5?',
                    'opsi_a' => '<!DOCTYPE html>',
                    'opsi_b' => '<html version="5.0">',
                    'opsi_c' => '<head>',
                    'opsi_d' => '<?xml version="1.0"?>',
                    'jawaban_benar' => 'a',
                    'bobot_nilai' => 25,
                    'urutan' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'quiz_id' => $quiz1Id,
                    'pertanyaan' => 'Manakah elemen HTML yang tepat untuk menampilkan judul utama paling besar pada halaman web?',
                    'opsi_a' => '<heading>',
                    'opsi_b' => '<h6>',
                    'opsi_c' => '<h1>',
                    'opsi_d' => '<title>',
                    'jawaban_benar' => 'c',
                    'bobot_nilai' => 25,
                    'urutan' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'quiz_id' => $quiz1Id,
                    'pertanyaan' => 'Atribut manakah yang digunakan pada elemen <a> untuk menentukan alamat URL tujuan tautan?',
                    'opsi_a' => 'src',
                    'opsi_b' => 'href',
                    'opsi_c' => 'link',
                    'opsi_d' => 'target',
                    'jawaban_benar' => 'b',
                    'bobot_nilai' => 25,
                    'urutan' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'quiz_id' => $quiz1Id,
                    'pertanyaan' => 'Di bagian manakah elemen <title> dan <meta> diletakkan pada struktur dokumen HTML?',
                    'opsi_a' => 'Di dalam elemen <body>',
                    'opsi_b' => 'Di dalam elemen <footer>',
                    'opsi_c' => 'Di dalam elemen <head>',
                    'opsi_d' => 'Setelah tag penutup </html>',
                    'jawaban_benar' => 'c',
                    'bobot_nilai' => 25,
                    'urutan' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $quiz2Id = DB::table('quizzes')->insertGetId([
                'guru_id' => $guruId,
                'judul_quiz' => 'Quiz Evaluasi Bab 2: Dasar CSS & Selector',
                'deskripsi' => 'Tantangan evaluasi penguasaan selector CSS (element, class, ID) serta properti penataan gaya dasar.',
                'durasi_menit' => 15,
                'kkm' => 75,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('quiz_questions')->insert([
                [
                    'quiz_id' => $quiz2Id,
                    'pertanyaan' => 'Simbol apakah yang digunakan untuk memilih elemen berdasarkan atribut class pada CSS?',
                    'opsi_a' => 'Tanda pagar (#)',
                    'opsi_b' => 'Tanda titik (.)',
                    'opsi_c' => 'Tanda bintang (*)',
                    'opsi_d' => 'Tanda titik dua (:)',
                    'jawaban_benar' => 'b',
                    'bobot_nilai' => 50,
                    'urutan' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'quiz_id' => $quiz2Id,
                    'pertanyaan' => 'Properti CSS manakah yang digunakan untuk mengubah warna latar belakang sebuah elemen?',
                    'opsi_a' => 'color',
                    'opsi_b' => 'text-color',
                    'opsi_c' => 'background-color',
                    'opsi_d' => 'border-color',
                    'jawaban_benar' => 'c',
                    'bobot_nilai' => 50,
                    'urutan' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_belajar');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
