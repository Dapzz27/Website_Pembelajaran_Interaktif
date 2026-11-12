<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruQuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['questions', 'attempts'])->orderBy('id', 'asc')->get();

        $totalQuiz = $quizzes->count();
        $totalSoal = QuizQuestion::count();
        $totalAttempts = QuizAttempt::count();
        $rataSkor = $totalAttempts > 0 ? (int) round(QuizAttempt::avg('skor')) : 0;

        return view('admin_guru.quiz', compact(
            'quizzes',
            'totalQuiz',
            'totalSoal',
            'totalAttempts',
            'rataSkor'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1|max:180',
            'kkm' => 'required|integer|min:0|max:100',
        ], [
            'judul_quiz.required' => 'Judul paket quiz wajib diisi.',
            'durasi_menit.required' => 'Batas waktu (menit) wajib diisi.',
        ]);

        Quiz::create([
            'guru_id' => Auth::id(),
            'judul_quiz' => trim($validated['judul_quiz']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'durasi_menit' => (int) $validated['durasi_menit'],
            'kkm' => (int) $validated['kkm'],
        ]);

        return back()->with('success', 'Paket Quiz baru berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1|max:180',
            'kkm' => 'required|integer|min:0|max:100',
        ]);

        $quiz = Quiz::findOrFail($id);
        $quiz->update([
            'judul_quiz' => trim($validated['judul_quiz']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'durasi_menit' => (int) $validated['durasi_menit'],
            'kkm' => (int) $validated['kkm'],
        ]);

        return back()->with('success', 'Paket Quiz berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        QuizQuestion::where('quiz_id', $quiz->id)->delete();
        QuizAttempt::where('quiz_id', $quiz->id)->delete();
        $quiz->delete();

        return back()->with('success', 'Paket Quiz beserta soalnya berhasil dihapus!');
    }

    public function storeQuestion(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'jawaban_benar' => 'required|in:a,b,c,d',
            'bobot_nilai' => 'required|integer|min:1|max:100',
        ]);

        $nextUrutan = ((int) QuizQuestion::where('quiz_id', $quiz->id)->max('urutan')) + 1;

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'pertanyaan' => trim($validated['pertanyaan']),
            'opsi_a' => trim($validated['opsi_a']),
            'opsi_b' => trim($validated['opsi_b']),
            'opsi_c' => trim($validated['opsi_c']),
            'opsi_d' => trim($validated['opsi_d']),
            'jawaban_benar' => $validated['jawaban_benar'],
            'bobot_nilai' => (int) $validated['bobot_nilai'],
            'urutan' => $nextUrutan,
        ]);

        return back()
            ->with('success', 'Butir soal berhasil ditambahkan ke ' . $quiz->judul_quiz . '!')
            ->with('open_quiz_id', $quiz->id);
    }

    public function updateQuestion(Request $request, $quizId, $questionId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $question = QuizQuestion::where('quiz_id', $quiz->id)->findOrFail($questionId);

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'jawaban_benar' => 'required|in:a,b,c,d',
            'bobot_nilai' => 'required|integer|min:1|max:100',
        ]);

        $question->update([
            'pertanyaan' => trim($validated['pertanyaan']),
            'opsi_a' => trim($validated['opsi_a']),
            'opsi_b' => trim($validated['opsi_b']),
            'opsi_c' => trim($validated['opsi_c']),
            'opsi_d' => trim($validated['opsi_d']),
            'jawaban_benar' => $validated['jawaban_benar'],
            'bobot_nilai' => (int) $validated['bobot_nilai'],
        ]);

        return back()
            ->with('success', 'Butir soal berhasil diperbarui!')
            ->with('open_quiz_id', $quiz->id);
    }

    public function destroyQuestion($quizId, $questionId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $question = QuizQuestion::where('quiz_id', $quiz->id)->findOrFail($questionId);
        $question->delete();

        $remaining = QuizQuestion::where('quiz_id', $quiz->id)->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();
        foreach ($remaining as $idx => $q) {
            $q->urutan = $idx + 1;
            $q->saveQuietly();
        }

        return back()
            ->with('success', 'Butir soal berhasil dihapus!')
            ->with('open_quiz_id', $quiz->id);
    }
}
