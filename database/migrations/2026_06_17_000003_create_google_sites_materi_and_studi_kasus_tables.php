<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lengkapi tabel `materi` dengan `created_by`
        if (Schema::hasTable('materi')) {
            Schema::table('materi', function (Blueprint $table) {
                if (!Schema::hasColumn('materi', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('guru_id');
                }
            });

            DB::table('materi')->whereNull('created_by')->update([
                'created_by' => DB::raw('guru_id'),
            ]);
        }

        // 2. Buat tabel `bagian_materi` (Blok Materi ala Google Sites)
        if (!Schema::hasTable('bagian_materi')) {
            Schema::create('bagian_materi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('materi_id');
                $table->string('tipe_layout', 50)->default('teks_penuh'); // teks_penuh | media_kiri | media_kanan | media_penuh
                $table->string('sub_judul')->nullable();
                $table->longText('isi_teks')->nullable();
                $table->string('tipe_media', 30)->default('none'); // none | gambar | video
                $table->string('file_gambar')->nullable();
                $table->string('url_video', 500)->nullable();
                $table->integer('urutan_blok')->default(1);
                $table->timestamps();

                $table->index(['materi_id', 'urutan_blok']);
            });
        }

        // 3. Buat tabel `studi_kasus`
        if (!Schema::hasTable('studi_kasus')) {
            Schema::create('studi_kasus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('materi_id');
                $table->string('judul_studi_kasus')->nullable();
                $table->string('tipe_studi_kasus', 100)->default('Esai singkat');
                $table->longText('instruksi_soal')->nullable();
                $table->string('opsi_a')->nullable();
                $table->string('opsi_b')->nullable();
                $table->string('opsi_c')->nullable();
                $table->string('opsi_d')->nullable();
                $table->string('kunci_jawaban', 50)->nullable();
                $table->timestamps();

                $table->index('materi_id');
            });
        }

        // 4. Buat tabel `log_baca_materi` (Pelacakan Real-Time Bagian & Media Terbaca)
        if (!Schema::hasTable('log_baca_materi')) {
            Schema::create('log_baca_materi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_id');
                $table->unsignedBigInteger('materi_id');
                $table->longText('bagian_terbaca_json')->nullable();
                $table->string('status_baca', 40)->default('belum_selesai'); // belum_selesai | sedang_dibaca | selesai
                $table->timestamp('waktu_selesai_baca')->nullable();
                $table->timestamps();

                $table->unique(['siswa_id', 'materi_id']);
            });
        }

        // 5. Buat tabel `pengumpulan_studi_kasus` (Jawaban Teks & File Lampiran Siswa)
        if (!Schema::hasTable('pengumpulan_studi_kasus')) {
            Schema::create('pengumpulan_studi_kasus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('studi_kasus_id');
                $table->unsignedBigInteger('siswa_id');
                $table->longText('jawaban_teks')->nullable();
                $table->string('file_lampiran')->nullable();
                $table->string('nama_file_asli')->nullable();
                $table->string('tipe_file', 50)->nullable();
                $table->timestamp('tanggal_kumpul')->nullable();
                $table->string('status', 40)->default('terkumpul'); // terkumpul | dinilai
                $table->timestamps();

                $table->index(['studi_kasus_id', 'siswa_id']);
            });
        }

        // Backfill data `materi` yang sudah ada ke `bagian_materi` & `studi_kasus`
        $existingMateri = DB::table('materi')->orderBy('urutan', 'asc')->get();
        foreach ($existingMateri as $m) {
            $hasBlocks = DB::table('bagian_materi')->where('materi_id', $m->id)->exists();
            if (!$hasBlocks) {
                $hasVideo = !empty($m->video_url) || (bool) $m->has_video;
                $videoUrl = !empty($m->video_url) ? $m->video_url : 'https://www.youtube.com/watch?v=UB1O30fR-EE';
                $teks = $m->konten_materi ?: $m->deskripsi ?: 'Pelajari konsep dasar pada materi ini secara menyeluruh.';

                if ($hasVideo) {
                    DB::table('bagian_materi')->insert([
                        'materi_id' => $m->id,
                        'tipe_layout' => 'media_kiri',
                        'sub_judul' => 'Pengantar & Video Pembelajaran: ' . ($m->judul_materi ?: $m->judul),
                        'isi_teks' => $teks,
                        'tipe_media' => 'video',
                        'file_gambar' => null,
                        'url_video' => $videoUrl,
                        'urutan_blok' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('bagian_materi')->insert([
                        'materi_id' => $m->id,
                        'tipe_layout' => 'teks_penuh',
                        'sub_judul' => 'Konsep Utama: ' . ($m->judul_materi ?: $m->judul),
                        'isi_teks' => $teks,
                        'tipe_media' => 'none',
                        'file_gambar' => null,
                        'url_video' => null,
                        'urutan_blok' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $hasKasus = DB::table('studi_kasus')->where('materi_id', $m->id)->exists();
            if (!$hasKasus && !empty(trim((string) $m->tipe_studi_kasus))) {
                DB::table('studi_kasus')->insert([
                    'materi_id' => $m->id,
                    'judul_studi_kasus' => 'Studi Kasus: ' . ($m->judul_materi ?: $m->judul),
                    'tipe_studi_kasus' => $m->tipe_studi_kasus,
                    'instruksi_soal' => $m->soal_studi_kasus ?: 'Selesaikan studi kasus berikut berdasarkan materi di atas.',
                    'opsi_a' => $m->opsi_a ?? null,
                    'opsi_b' => $m->opsi_b ?? null,
                    'opsi_c' => $m->opsi_c ?? null,
                    'opsi_d' => $m->opsi_d ?? null,
                    'kunci_jawaban' => $m->kunci_studi_kasus ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_studi_kasus');
        Schema::dropIfExists('log_baca_materi');
        Schema::dropIfExists('studi_kasus');
        Schema::dropIfExists('bagian_materi');
    }
};
