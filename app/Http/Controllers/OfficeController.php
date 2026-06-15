<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\Chemical;
use App\Models\Machinery;
use App\Models\PeticashTransaction;
use App\Models\Worksite;
use App\Models\Worker;
use App\Models\WorkerSalary;
use App\Models\OfficeStaffSalary;
use App\Models\Vehicle;
use App\Models\Jewellery;
use App\Models\Property;
use App\Models\OtherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class OfficeController extends Controller
{
    public function worksites()
    {
        return Response::json(Worksite::all());
    }

    public function worksite(Worksite $worksite)
    {
        return Response::json($worksite);
    }

    public function createWorksite(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $worksite = Worksite::create($payload);

        return Response::json($worksite, 201);
    }

    public function updateWorksite(Request $request, Worksite $worksite)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $worksite->update($payload);

        return Response::json($worksite->refresh());
    }

    public function deleteWorksite(Worksite $worksite)
    {
        $worksite->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }

    public function workers()
    {
        return Response::json(Worker::with('worksite')->paginate(50));
    }

    public function createWorker(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'assigned_worksite_id' => 'nullable|exists:worksites,id',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|string|max:50',
            'nic' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:16',
            'join_date' => 'nullable|date',
            'face_recognition_enabled' => 'boolean',
        ]);

        $worker = Worker::create($payload);

        return Response::json($worker->load('worksite'));
    }

    public function updateWorker(Request $request, Worker $worker)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'assigned_worksite_id' => 'nullable|exists:worksites,id',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|string|max:50',
            'nic' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:16',
            'join_date' => 'nullable|date',
            'face_recognition_enabled' => 'boolean',
        ]);

        $worker->update($payload);

        return Response::json($worker->refresh()->load('worksite'));
    }

    public function deleteWorker(Worker $worker)
    {
        $worker->delete();

        return Response::json(['deleted' => true]);
    }

    public function assets()
    {
        return Response::json(Asset::with('worksite')->paginate(50));
    }

    public function createAsset(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'count' => 'nullable|integer|min:0',
            'value' => 'nullable|numeric|min:0',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $asset = Asset::create($payload);

        return Response::json($asset);
    }

    public function updateAsset(Request $request, Asset $asset)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'count' => 'nullable|integer|min:0',
            'value' => 'nullable|numeric|min:0',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $asset->update($payload);

        return Response::json($asset->refresh());
    }

    public function deleteAsset(Asset $asset)
    {
        $asset->delete();

        return Response::json(['deleted' => true]);
    }

    public function machineries()
    {
        return Response::json(Machinery::with('worksite')->paginate(50));
    }

    public function createMachinery(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'maintenance_due_at' => 'nullable|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $machinery = Machinery::create($payload);

        return Response::json($machinery);
    }

    public function updateMachinery(Request $request, Machinery $machinery)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'maintenance_due_at' => 'nullable|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $machinery->update($payload);

        return Response::json($machinery->refresh());
    }

    public function deleteMachinery(Machinery $machinery)
    {
        $machinery->delete();

        return Response::json(['deleted' => true]);
    }

    public function chemicals()
    {
        return Response::json(Chemical::with('worksite')->paginate(50));
    }

    public function createChemical(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'hazard_level' => 'required|string|max:50',
            'storage_location' => 'nullable|string|max:255',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $chemical = Chemical::create($payload);

        return Response::json($chemical);
    }

    public function updateChemical(Request $request, Chemical $chemical)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'hazard_level' => 'required|string|max:50',
            'storage_location' => 'nullable|string|max:255',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $chemical->update($payload);

        return Response::json($chemical->refresh());
    }

    public function deleteChemical(Chemical $chemical)
    {
        $chemical->delete();

        return Response::json(['deleted' => true]);
    }

    public function approvals()
    {
        return Response::json(ApprovalRequest::with('worksite')->paginate(50));
    }

    public function createApproval(Request $request)
    {
        $payload = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:50',
            'requested_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'worksite_id' => 'nullable|exists:worksites,id',
            'amount' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'holder' => 'nullable|string|max:255',
        ]);

        $approval = ApprovalRequest::create($payload);

        return Response::json($approval);
    }

    public function updateApproval(Request $request, ApprovalRequest $approval)
    {
        $payload = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:50',
            'requested_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'worksite_id' => 'nullable|exists:worksites,id',
            'amount' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'holder' => 'nullable|string|max:255',
        ]);

        $approval->update($payload);

        return Response::json($approval->refresh());
    }

    public function deleteApproval(ApprovalRequest $approval)
    {
        $approval->delete();

        return Response::json(['deleted' => true]);
    }

    public function peticash()
    {
        return Response::json(PeticashTransaction::with('worksite')->paginate(50));
    }

    public function createPeticash(Request $request)
    {
        $payload = $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $transaction = PeticashTransaction::create($payload);

        return Response::json($transaction);
    }

    public function updatePeticash(Request $request, PeticashTransaction $peticashTransaction)
    {
        $payload = $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $peticashTransaction->update($payload);

        return Response::json($peticashTransaction->refresh());
    }

    public function deletePeticash(PeticashTransaction $peticashTransaction)
    {
        $peticashTransaction->delete();

        return Response::json(['deleted' => true]);
    }

    public function workerSalaries()
    {
        return Response::json(WorkerSalary::with('worker')->paginate(50));
    }

    public function createWorkerSalary(Request $request)
    {
        $payload = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'salary' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annual,hourly',
            'date' => 'required|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $workerSalary = WorkerSalary::create($payload);

        return Response::json($workerSalary->load('worker'));
    }

    public function updateWorkerSalary(Request $request, WorkerSalary $workerSalary)
    {
        $payload = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'salary' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annual,hourly',
            'date' => 'required|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $workerSalary->update($payload);

        return Response::json($workerSalary->refresh()->load('worker'));
    }

    public function deleteWorkerSalary(WorkerSalary $workerSalary)
    {
        $workerSalary->delete();

        return Response::json(['deleted' => true]);
    }

    public function officeSalaries()
    {
        return Response::json(OfficeStaffSalary::with('worksite')->paginate(50));
    }

    public function createOfficeSalary(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annual,hourly',
            'date' => 'required|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $officeSalary = OfficeStaffSalary::create($payload);

        return Response::json($officeSalary);
    }

    public function updateOfficeSalary(Request $request, OfficeStaffSalary $officeSalary)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annual,hourly',
            'date' => 'required|date',
            'worksite_id' => 'nullable|exists:worksites,id',
        ]);

        $officeSalary->update($payload);

        return Response::json($officeSalary->refresh());
    }

    public function deleteOfficeSalary(OfficeStaffSalary $officeSalary)
    {
        $officeSalary->delete();

        return Response::json(['deleted' => true]);
    }

    // Personal Assets Routes - Vehicles
    public function vehicles()
    {
        return Response::json(['data' => Vehicle::paginate(50)]);
    }

    public function createVehicle(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'plateNo' => 'required|string|max:255',
        ]);

        $vehicle = Vehicle::create($payload);

        return Response::json(['data' => $vehicle]);
    }

    public function updateVehicle(Request $request, Vehicle $vehicle)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'plateNo' => 'required|string|max:255',
        ]);

        $vehicle->update($payload);

        return Response::json(['data' => $vehicle->refresh()]);
    }

    public function deleteVehicle(Vehicle $vehicle)
    {
        $vehicle->delete();

        return Response::json(['deleted' => true]);
    }

    // Personal Assets Routes - Jewelleries
    public function jewelleries()
    {
        return Response::json(['data' => Jewellery::paginate(50)]);
    }

    public function createJewellery(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'weight' => 'required|string|max:255',
        ]);

        $jewellery = Jewellery::create($payload);

        return Response::json(['data' => $jewellery]);
    }

    public function updateJewellery(Request $request, Jewellery $jewellery)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'weight' => 'required|string|max:255',
        ]);

        $jewellery->update($payload);

        return Response::json(['data' => $jewellery->refresh()]);
    }

    public function deleteJewellery(Jewellery $jewellery)
    {
        $jewellery->delete();

        return Response::json(['deleted' => true]);
    }

    // Personal Assets Routes - Properties
    public function properties()
    {
        return Response::json(['data' => Property::paginate(50)]);
    }

    public function createProperty(Request $request)
    {
        $payload = $request->validate([
            'location' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        $property = Property::create($payload);

        return Response::json(['data' => $property]);
    }

    public function updateProperty(Request $request, Property $property)
    {
        $payload = $request->validate([
            'location' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        $property->update($payload);

        return Response::json(['data' => $property->refresh()]);
    }

    public function deleteProperty(Property $property)
    {
        $property->delete();

        return Response::json(['deleted' => true]);
    }

    // Approval Actions
    public function approveApproval(Request $request, ApprovalRequest $approval)
    {
        $payload = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $approval->update([
            'status' => 'approved',
        ]);

        return Response::json(['data' => $approval->refresh()]);
    }

    // Worker Actions
    public function terminateWorker(Request $request, Worker $worker)
    {
        $worker->update([
            'status' => 'Terminated',
        ]);

        return Response::json(['data' => $worker->refresh()]);
    }

    // Other Payments
    public function otherPayments(Request $request)
    {
        return Response::json(['data' => OtherPayment::latest()->get()]);
    }

    public function createOtherPayment(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'data' => 'nullable|string',
            'amount' => 'required|numeric',
            'anouny' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $payment = OtherPayment::create($validated);
        return Response::json(['data' => $payment], 201);
    }

    public function updateOtherPayment(Request $request, OtherPayment $otherPayment)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'data' => 'nullable|string',
            'amount' => 'required|numeric',
            'anouny' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $otherPayment->update($validated);
        return Response::json(['data' => $otherPayment]);
    }

    public function deleteOtherPayment(OtherPayment $otherPayment)
    {
        $otherPayment->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }

    // -----------------------------------------------------------------------
    // Face Recognition & Attendance
    // -----------------------------------------------------------------------

    /** URL of the Python face_service.py micro-service */
    private string $faceServiceUrl = 'http://127.0.0.1:5050';

    /**
     * Upload a worker photo and register the face with the Python service.
     * Office staff call this when creating / editing a worker.
     *
     * POST /workers/{worker}/upload-face
     * Body: multipart/form-data with field "photo" (image file)
     */
    public function uploadWorkerFace(Request $request, Worker $worker)
    {
        $request->validate([
            'photo' => 'required|image|max:5120', // max 5 MB
        ]);

        // Store photo on disk
        $path = $request->file('photo')->store("worker_faces/{$worker->id}", 'public');

        // Convert to base64 so the face service can process it
        $fullPath = Storage::disk('public')->path($path);
        $base64   = base64_encode(file_get_contents($fullPath));
        $ext      = $request->file('photo')->extension();
        $dataUri  = "data:image/{$ext};base64,{$base64}";

        // Register face with Python service
        try {
            $fsResponse = Http::timeout(30)->post("{$this->faceServiceUrl}/register", [
                'worker_id'    => $worker->id,
                'worker_name'  => $worker->name,
                'image_base64' => $dataUri,
            ]);

            if (!$fsResponse->successful()) {
                return Response::json([
                    'success' => false,
                    'error'   => 'Face service error: ' . $fsResponse->body(),
                ], 422);
            }
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error'   => 'Face service unavailable: ' . $e->getMessage(),
            ], 503);
        }

        // Save photo path in DB and enable face recognition
        $worker->update([
            'face_photo_path'         => $path,
            'face_recognition_enabled' => true,
        ]);

        return Response::json([
            'success'          => true,
            'face_photo_path'  => $path,
            'worker'           => $worker->refresh()->load('worksite'),
        ]);
    }

    /**
     * Supervisor sends a captured image; the face service matches it against
     * registered workers and returns the worker ID + name.
     *
     * POST /face-recognition/recognize
     * Body JSON: { "image_base64": "<data-uri or raw base64>" }
     */
    public function recognizeFace(Request $request)
    {
        $request->validate([
            'image_base64' => 'required|string',
        ]);

        try {
            $fsResponse = Http::timeout(30)->post("{$this->faceServiceUrl}/recognize", [
                'image_base64' => $request->input('image_base64'),
            ]);

            return Response::json($fsResponse->json(), $fsResponse->status());
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error'   => 'Face service unavailable: ' . $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Mark attendance for a worker (called after face recognition succeeds).
     *
     * POST /attendances
     * Body JSON:
     *   { "worker_id", "worksite_id"?, "shift", "date", "method"?, "confidence"? }
     */
    public function markAttendance(Request $request)
    {
        $payload = $request->validate([
            'worker_id'   => 'required|exists:workers,id',
            'worksite_id' => 'nullable|exists:worksites,id',
            'shift'       => 'required|in:Morning,Evening',
            'date'        => 'required|date',
            'method'      => 'nullable|string|max:20',
            'confidence'  => 'nullable|numeric',
        ]);

        $worker = Worker::find($payload['worker_id']);
        if ($payload['worksite_id'] && $worker->assigned_worksite_id != $payload['worksite_id']) {
            return Response::json([
                'success' => false,
                'error'   => 'Worker is not assigned to this worksite.'
            ], 403);
        }

        $payload['marked_at'] = now();
        $payload['status']    = 'present';
        $payload['method']    = $payload['method'] ?? 'face';

        $existing = Attendance::where([
            'worker_id' => $payload['worker_id'],
            'date'      => $payload['date'],
            'shift'     => $payload['shift'],
        ])->first();

        if ($existing) {
            return Response::json([
                'success' => false,
                'error'   => 'Attendance already marked for this shift. Delete the existing record to mark again.'
            ], 400);
        }

        $attendance = Attendance::create($payload);

        return Response::json([
            'success'    => true,
            'attendance' => $attendance->load('worker', 'worksite'),
        ], 201);
    }

    /**
     * List attendance records (optionally filtered by worksite / date).
     *
     * GET /attendances?worksite_id=1&date=2026-06-12
     */
    public function attendances(Request $request)
    {
        $query = Attendance::with('worker', 'worksite')->latest('marked_at');

        if ($request->filled('worksite_id')) {
            $query->where('worksite_id', $request->worksite_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        return Response::json($query->paginate(50));
    }

    /**
     * Delete a single attendance record.
     *
     * DELETE /attendances/{attendance}
     */
    public function deleteAttendance(Attendance $attendance)
    {
        $attendance->delete();
        return Response::json(['success' => true]);
    }
}
