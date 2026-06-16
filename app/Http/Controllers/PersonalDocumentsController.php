<?php

namespace App\Http\Controllers;

use App\Models\PersonalNote;
use App\Models\PersonalFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonalDocumentsController extends Controller
{
    // --- NOTES ---

    public function getNotes()
    {
        $notes = PersonalNote::orderBy('id', 'desc')->get();
        return response()->json($notes);
    }

    public function storeNote(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'date' => 'nullable|string'
        ]);

        $note = PersonalNote::create($request->all());
        return response()->json($note, 201);
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string',
            'date' => 'nullable|string'
        ]);

        $note = PersonalNote::findOrFail($id);
        $note->update($request->all());
        return response()->json($note);
    }

    public function deleteNote($id)
    {
        $note = PersonalNote::findOrFail($id);
        $note->delete();
        return response()->json(['message' => 'Note deleted successfully']);
    }

    // --- FILES ---

    public function getFiles()
    {
        $files = PersonalFile::orderBy('id', 'desc')->get()->map(function ($file) {
            if ($file->file_path) {
                $file->file_url = url('storage/' . $file->file_path);
            } else {
                $file->file_url = null;
            }
            return $file;
        });
        return response()->json($files);
    }

    public function storeFile(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'type'        => 'required|string|in:PDF,WORD,IMG',
            'uploaded_at' => 'nullable|string',
            'file'        => 'nullable|file|max:20480', // 20MB max
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('personal-files', 'public');
        }

        $file = PersonalFile::create([
            'name'        => $request->name,
            'type'        => $request->type,
            'uploaded_at' => $request->uploaded_at ?? now()->format('d/m/Y'),
            'file_path'   => $filePath,
        ]);

        if ($file->file_path) {
            $file->file_url = url('storage/' . $file->file_path);
        }

        return response()->json($file, 201);
    }

    public function updateFile(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string',
            'type'        => 'required|string|in:PDF,WORD,IMG',
            'uploaded_at' => 'nullable|string',
            'file'        => 'nullable|file|max:20480',
        ]);

        $file = PersonalFile::findOrFail($id);

        $filePath = $file->file_path;
        if ($request->hasFile('file')) {
            // Delete old file
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('personal-files', 'public');
        }

        $file->update([
            'name'        => $request->name,
            'type'        => $request->type,
            'uploaded_at' => $request->uploaded_at ?? $file->uploaded_at,
            'file_path'   => $filePath,
        ]);

        if ($file->file_path) {
            $file->file_url = url('storage/' . $file->file_path);
        }

        return response()->json($file);
    }

    public function deleteFile($id)
    {
        $file = PersonalFile::findOrFail($id);

        if ($file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }
}
