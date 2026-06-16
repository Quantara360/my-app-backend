<?php

namespace App\Http\Controllers;

use App\Models\PersonalNote;
use App\Models\PersonalFile;
use Illuminate\Http\Request;

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
        $files = PersonalFile::orderBy('id', 'desc')->get();
        return response()->json($files);
    }

    public function storeFile(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'uploaded_at' => 'nullable|string'
        ]);

        $file = PersonalFile::create($request->all());
        return response()->json($file, 201);
    }

    public function updateFile(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'uploaded_at' => 'nullable|string'
        ]);

        $file = PersonalFile::findOrFail($id);
        $file->update($request->all());
        return response()->json($file);
    }

    public function deleteFile($id)
    {
        $file = PersonalFile::findOrFail($id);
        $file->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }
}
