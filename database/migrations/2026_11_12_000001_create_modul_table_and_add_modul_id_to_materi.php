<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modul')) {
            Schema::create('modul', function (Blueprint $table) {
                $table->id();
                $table->string('judul_modul');
                $table->string('kategori_atau_kelas', 150)->nullable();
                $table->text('deskripsi_singkat')->nullable();
                $table->string('thumbnail_atau_ikon')->nullable();
                $table->unsignedInteger('urutan_modul')->default(1);
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('materi') && !Schema::hasColumn('materi', 'modul_id')) {
            Schema::table('materi', function (Blueprint $table) {
                $table->unsignedBigInteger('modul_id')
                    ->nullable()
                    ->after('id')
                    ->index();
            });
        }

        // Migrasi Data Aman (Anti-Error):
        // Jika sudah ada data materi atau tabel modul masih kosong, buat modul awal "Pemrograman Web Dasar"
        // dan hubungkan seluruh materi yang belum memiliki modul_id ke modul tersebut.
        if (Schema::hasTable('modul') && Schema::hasTable('materi')) {
            $firstGuruId = Schema::hasTable('users')
                ? DB::table('users')->where('role', 1)->value('id')
                : null;

            $defaultModulId = DB::table('modul')->orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->value('id');

            $hasOrphanMateri = DB::table('materi')->whereNull('modul_id')->exists();
            $modulEmpty = DB::table('modul')->count() === 0;

            if (!$defaultModulId && ($hasOrphanMateri || $modulEmpty)) {
                $defaultModulId = DB::table('modul')->insertGetId([
                    'judul_modul' => 'Pemrograman Web Dasar',
                    'kategori_atau_kelas' => 'Kelas X / XI RPL',
                    'deskripsi_singkat' => 'Fondasi pengembangan antarmuka web modern mencakup struktur dokumen HTML, penggunaan tag dan atribut, elemen semantik, serta studi kasus pembuatan halaman web.',
                    'thumbnail_atau_ikon' => 'code',
                    'urutan_modul' => 1,
                    'created_by' => $firstGuruId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($defaultModulId) {
                DB::table('materi')
                    ->whereNull('modul_id')
                    ->update(['modul_id' => $defaultModulId]);

                // Pastikan urutan materi di dalam modul awal terurut rapi 1..N
                $items = DB::table('materi')
                    ->where('modul_id', $defaultModulId)
                    ->orderBy('urutan', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($items as $idx => $item) {
                    DB::table('materi')
                        ->where('id', $item->id)
                        ->update(['urutan' => $idx + 1]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('materi') && Schema::hasColumn('materi', 'modul_id')) {
            Schema::table('materi', function (Blueprint $table) {
                $table->dropColumn('modul_id');
            });
        }

        Schema::dropIfExists('modul');
    }
};
