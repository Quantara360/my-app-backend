<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\Chemical;
use App\Models\Machinery;
use App\Models\PeticashTransaction;
use App\Models\Hospital;
use App\Models\SubSite;
use App\Models\SubSiteImage;
use App\Models\Worksite;
use App\Models\WorksiteReport;
use App\Models\Worker;
use App\Models\WorkerSalary;
use App\Models\OfficeStaffSalary;
use App\Models\Vehicle;
use App\Models\Jewellery;
use App\Models\Property;
use App\Models\OtherPayment;
use App\Models\BidBond;
use App\Models\PerformanceBond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class OfficeController extends Controller
{
    public function worksites(Request $request)
    {
        $query = Worksite::query();
        
        if ($request->has('parent_id')) {
            // allows finding children of a specific worksite. Use parent_id=null for root sites
            $parentId = $request->query('parent_id');
            if (strtolower($parentId) === 'null' || $parentId === '') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        return Response::json($query->get()->map(function ($site) {
            $site->parent_id = $site->parent_id !== null && $site->parent_id !== '' ? (int) $site->parent_id : null;
            return $site;
        }));
    }

    public function worksite(Worksite $worksite)
    {
        return Response::json($worksite);
    }

    public function createWorksite(Request $request)
    {
        $payload = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'supervisor_id' => 'nullable|exists:users,id',
            'logo_base64'   => 'nullable|string',
        ]);

        if (!empty($payload['logo_base64'])) {
            $image_parts = explode(";base64,", $payload['logo_base64']);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'png';
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'worksites/' . uniqid() . '.' . $image_type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $image_base64);
                $payload['logo'] = '/storage/' . $fileName;
            }
            unset($payload['logo_base64']);
        }

        $worksite = Worksite::create($payload);

        return Response::json($worksite, 201);
    }

    public function updateWorksite(Request $request, Worksite $worksite)
    {
        $payload = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'supervisor_id' => 'nullable|exists:users,id',
            'logo_base64'   => 'nullable|string',
        ]);

        if (!empty($payload['logo_base64'])) {
            $image_parts = explode(";base64,", $payload['logo_base64']);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'png';
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'worksites/' . uniqid() . '.' . $image_type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $image_base64);
                $payload['logo'] = '/storage/' . $fileName;
            }
            unset($payload['logo_base64']);
        }

        $worksite->update($payload);

        return Response::json($worksite->refresh());
    }

    public function deleteWorksite(Worksite $worksite)
    {
        $worksite->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }

    // ─── Hospitals ────────────────────────────────────────────────────────────

    public function hospitals(Request $request)
    {
        $query = Hospital::query();
        if ($request->has('worksite_id')) {
            $query->where('worksite_id', $request->query('worksite_id'));
        }
        return Response::json($query->get());
    }

    public function createHospital(Request $request)
    {
        $payload = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'worksite_id' => 'required|exists:worksites,id',
        ]);

        $hospital = Hospital::create($payload);
        return Response::json($hospital, 201);
    }

    public function updateHospital(Request $request, Hospital $hospital)
    {
        $payload = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'worksite_id' => 'sometimes|exists:worksites,id',
        ]);

        $hospital->update($payload);
        return Response::json($hospital->refresh());
    }

    public function deleteHospital(Hospital $hospital)
    {
        $hospital->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }

    // ─── Sub Sites ────────────────────────────────────────────────────────────

    public function subSites(Request $request)
    {
        $query = SubSite::query();
        if ($request->has('hospital_id')) {
            $query->where('hospital_id', $request->query('hospital_id'));
        }
        return Response::json($query->get());
    }

    public function createSubSite(Request $request)
    {
        $payload = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        $subSite = SubSite::create($payload);
        return Response::json($subSite, 201);
    }

    public function updateSubSite(Request $request, SubSite $subSite)
    {
        $payload = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'hospital_id' => 'sometimes|exists:hospitals,id',
        ]);

        $subSite->update($payload);
        return Response::json($subSite->refresh());
    }

    public function deleteSubSite(SubSite $subSite)
    {
        $subSite->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }

    // ─── Sub Site Images ───────────────────────────────────────────────────────

    public function getBookImages(Request $request)
    {
        $request->validate([
            'sub_site_id' => 'required|integer',
        ]);

        $query = SubSiteImage::where('sub_site_id', $request->query('sub_site_id'))
                             ->where('created_at', '>=', now()->subHours(24));

        if ($request->has('book_id')) {
            $query->where('book_id', $request->query('book_id'));
        }

        return Response::json($query->latest()->get());
    }

    public function uploadBookImage(Request $request)
    {
        $request->validate([
            'sub_site_id' => 'required|integer',
            'book_id' => 'required|integer',
            'photo' => 'required|image|max:10240', // 10MB max
        ]);

        $subSiteId = $request->input('sub_site_id');
        $bookId = $request->input('book_id');

        $activeImagesCount = SubSiteImage::where('sub_site_id', $subSiteId)
                                         ->where('book_id', $bookId)
                                         ->where('created_at', '>=', now()->subHours(24))
                                         ->count();

        if ($activeImagesCount >= 10) {
            return Response::json(['error' => 'Maximum limit of 10 images reached for this book within the last 24 hours.'], 422);
        }

        $path = $request->file('photo')->store("sub_site_images/{$subSiteId}/book_{$bookId}", 'public');

        $image = SubSiteImage::create([
            'sub_site_id' => $subSiteId,
            'book_id' => $bookId,
            'image_path' => $path,
        ]);

        return Response::json($image, 201);
    }

    public function deleteBookImage(SubSiteImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return Response::json(['message' => 'Deleted successfully']);
    }

    public function workers()
    {
        return Response::json(Worker::with(['worksite', 'epfHistories'])->paginate(50));
    }

    public function workerEpfHistory(Worker $worker)
    {
        return Response::json($worker->epfHistories()->latest()->get());
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
            'epf' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
        ]);

        $worker = Worker::create($payload);

        if (!empty($payload['epf'])) {
            $worker->epfHistories()->create(['epf_number' => $payload['epf']]);
        }

        return Response::json($worker->load(['worksite', 'epfHistories']));
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
            'epf' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
        ]);

        $oldEpf = $worker->epf;
        $worker->update($payload);

        if (!empty($payload['epf']) && $payload['epf'] !== $oldEpf) {
            $worker->epfHistories()->create(['epf_number' => $payload['epf']]);
        }

        return Response::json($worker->refresh()->load(['worksite', 'epfHistories']));
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
            'tender_requirements' => 'nullable|string|max:255',
            'monthly_purchases' => 'nullable|string|max:255',
            'balance' => 'nullable|string|max:255',
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
            'tender_requirements' => 'nullable|string|max:255',
            'monthly_purchases' => 'nullable|string|max:255',
            'balance' => 'nullable|string|max:255',
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

    /** URL of the Python face_service.py micro-service (set FACE_SERVICE_URL in .env) */
    private string $faceServiceUrl;

    public function __construct()
    {
        $this->faceServiceUrl = env('FACE_SERVICE_URL', 'http://ai:5050');
    }

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
        // Normalize shift casing before validation
        if ($request->has('shift')) {
            $shift = ucfirst(strtolower($request->input('shift')));
            $request->merge(['shift' => $shift]);
        }
        // Normalize state casing
        if ($request->has('state')) {
            $request->merge(['state' => strtoupper($request->input('state'))]);
        }

        // Debug: log incoming data
        \Log::info('[markAttendance] incoming', $request->all());

        try {
            $payload = $request->validate([
                'worker_id'   => 'required|exists:workers,id',
                'worksite_id' => 'nullable',           // accept anything — we override below
                'sub_site_id' => 'nullable|exists:sub_sites,id',
                'shift'       => 'required|in:Morning,Evening',
                'date'        => 'required|date',
                'method'      => 'nullable|string|max:20',
                'confidence'  => 'nullable|numeric',
                'state'       => 'nullable|in:IN,OUT',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('[markAttendance] validation failed', $e->errors());
            return Response::json([
                'success' => false,
                'errors'  => $e->errors(),
                'error'   => 'Validation failed: ' . implode(', ', array_map(fn($msgs) => implode(', ', $msgs), $e->errors())),
            ], 422);
        }

        $state = $payload['state'] ?? 'IN';
        $worker = Worker::find($payload['worker_id']);

        // Always use the worker's real assigned worksite — don't trust the frontend param
        $payload['worksite_id'] = $worker->assigned_worksite_id;


        $existing = Attendance::where([
            'worker_id' => $payload['worker_id'],
            'shift'     => $payload['shift'],
        ])->whereDate('date', $payload['date'])->first();

        $now = now();

        if ($state === 'IN') {
            if ($existing) {
                return Response::json([
                    'success' => false,
                    'error'   => "IN attendance already marked for the {$payload['shift']} shift."
                ], 400);
            }

            // Status Logic: Present if <= 7:00 AM/PM, Late if > 7:00 AM/PM
            $status = 'present';
            $localTime = $now->copy()->timezone('Asia/Colombo');
            $hour = (int)$localTime->format('H');
            $minute = (int)$localTime->format('i');

            if ($payload['shift'] === 'Morning') {
                if ($hour > 7 || ($hour === 7 && $minute > 0)) {
                    $status = 'late';
                }
            } else { // Evening
                if ($hour > 19 || ($hour === 19 && $minute > 0)) {
                    $status = 'late';
                }
            }
            $payload['marked_at'] = $now;
            $payload['status']    = $status;
            $payload['method']    = $payload['method'] ?? 'face';

            $attendance = Attendance::create($payload);

            return Response::json([
                'success'    => true,
                'attendance' => $attendance->load('worker', 'worksite', 'subSite'),
            ], 201);
        } else { // OUT
            if (!$existing) {
                return Response::json([
                    'success' => false,
                    'error'   => "You must mark IN for the {$payload['shift']} shift before marking OUT."
                ], 400);
            }

            if ($existing->out_marked_at) {
                return Response::json([
                    'success' => false,
                    'error'   => "OUT attendance already marked for the {$payload['shift']} shift."
                ], 400);
            }

            $existing->update([
                'out_marked_at'  => $now,
                'out_method'     => $payload['method'] ?? 'face',
                'out_confidence' => $payload['confidence'],
            ]);

            return Response::json([
                'success'    => true,
                'attendance' => $existing->load('worker', 'worksite'),
            ], 200);
        }
    }

    /**
     * List attendance records (optionally filtered by worksite / date).
     * When include_absents=1 is passed together with worksite_id, date, and shift,
     * the response also includes synthetic "absent" records for workers who did not
     * mark IN once the shift window has closed.
     *
     * GET /attendances?worksite_id=1&date=2026-06-25&shift=Morning&include_absents=1
     */
    public function attendances(Request $request)
    {
        $query = Attendance::with('worker', 'worksite', 'subSite')->latest('marked_at');

        if ($request->filled('worksite_id')) {
            $query->where('worksite_id', $request->worksite_id);
        }
        if ($request->filled('sub_site_id')) {
            $query->where('sub_site_id', $request->sub_site_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }
        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        // ── Absent-worker generation ───────────────────────────────────────────
        if (
            $request->boolean('include_absents')
            && $request->filled('date')
            && $request->filled('shift')
        ) {
            $date    = $request->input('date');      // e.g. "2026-06-25"
            $shift   = $request->input('shift');     // "Morning" | "Evening"
            $wsId    = (int) $request->input('worksite_id');
            $tz      = 'Asia/Colombo';
            $now     = \Carbon\Carbon::now($tz);
            $attDate = \Carbon\Carbon::createFromFormat('Y-m-d', $date, $tz)->startOfDay();

            // Determine whether the shift window has fully closed
            if ($shift === 'Morning') {
                // Morning shift: 00:00 – 18:00 on attendance date
                $shiftEnd = $attDate->copy()->setHour(18)->setMinute(0)->setSecond(0);
            } else {
                // Evening shift: starts same day, ends 06:00 next day
                $shiftEnd = $attDate->copy()->addDay()->setHour(6)->setMinute(0)->setSecond(0);
            }

            $shiftEnded = $now->gte($shiftEnd);

            $realRecords = $query->get();
            $allRecords  = $realRecords->map(fn($r) => $r->toArray())->values()->toArray();

            if ($shiftEnded) {
                // Workers assigned to the main worksite who have NOT marked IN
                $markedIds = $realRecords->pluck('worker_id')->unique()->toArray();

                $absentQuery = \App\Models\Worker::where('status', 'active')
                    ->whereNotIn('id', $markedIds);
                
                if ($request->filled('worksite_id')) {
                    $absentQuery->where('assigned_worksite_id', $request->input('worksite_id'));
                }

                $absentWorkers = $absentQuery->get();

                foreach ($absentWorkers as $worker) {
                    $allRecords[] = [
                        'id'           => 'absent_' . $worker->id,
                        'worker_id'    => $worker->id,
                        'worksite_id'  => $worker->assigned_worksite_id,
                        'sub_site_id'  => null,
                        'shift'        => $shift,
                        'date'         => $date . 'T00:00:00.000000Z',
                        'marked_at'    => null,
                        'out_marked_at' => null,
                        'status'       => 'absent',
                        'method'       => null,
                        'worker'       => ['id' => $worker->id, 'name' => $worker->name],
                        'worksite'     => null,
                        'sub_site'     => null,
                    ];
                }
            }

            return Response::json(['data' => $allRecords, 'total' => count($allRecords)]);
        }
        // ─────────────────────────────────────────────────────────────────────

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

    /**
     * Save worksite report (workers count, date, up to 3 images)
     * POST /worksites/{worksite}/reports
     */
    public function saveWorksiteReport(Request $request, Worksite $worksite)
    {
        $data = $request->validate([
            'workers_count' => 'nullable|integer',
            'report_date'   => 'nullable|date',
            'image_1'       => 'nullable|image|max:10240', // max 10MB
            'image_2'       => 'nullable|image|max:10240',
            'image_3'       => 'nullable|image|max:10240',
        ]);

        $report = new WorksiteReport();
        $report->worksite_id = $worksite->id;
        $report->workers_count = $data['workers_count'] ?? null;
        $report->report_date = $data['report_date'] ?? null;

        if ($request->hasFile('image_1')) {
            $path = $request->file('image_1')->store('reports', 'public');
            $report->image_1 = Storage::url($path);
        }
        if ($request->hasFile('image_2')) {
            $path = $request->file('image_2')->store('reports', 'public');
            $report->image_2 = Storage::url($path);
        }
        if ($request->hasFile('image_3')) {
            $path = $request->file('image_3')->store('reports', 'public');
            $report->image_3 = Storage::url($path);
        }

        $report->save();

        return Response::json(['success' => true, 'report' => $report], 201);
    }

    // Bid Bonds
    public function bidBonds()
    {
        return Response::json(BidBond::orderBy('id', 'desc')->get());
    }

    public function createBidBond(Request $request)
    {
        $payload = $request->validate([
            'valid_period' => 'nullable|string',
            'tender_status' => 'nullable|string',
            'duration_date' => 'nullable|string',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'is_awarded' => 'nullable|boolean',
        ]);
        $bond = BidBond::create($payload);
        return Response::json($bond, 201);
    }

    public function updateBidBond(Request $request, BidBond $bidBond)
    {
        $payload = $request->validate([
            'valid_period' => 'nullable|string',
            'tender_status' => 'nullable|string',
            'duration_date' => 'nullable|string',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'is_awarded' => 'nullable|boolean',
        ]);
        $bidBond->update($payload);
        return Response::json($bidBond);
    }

    public function deleteBidBond(BidBond $bidBond)
    {
        $bidBond->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }

    // Performance Bonds
    public function performanceBonds()
    {
        return Response::json(PerformanceBond::orderBy('id', 'desc')->get());
    }

    public function createPerformanceBond(Request $request)
    {
        $payload = $request->validate([
            'valid_period' => 'nullable|string',
            'date' => 'nullable|string',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'tender_status' => 'nullable|string',
        ]);
        $bond = PerformanceBond::create($payload);
        return Response::json($bond, 201);
    }

    public function updatePerformanceBond(Request $request, PerformanceBond $performanceBond)
    {
        $payload = $request->validate([
            'valid_period' => 'nullable|string',
            'date' => 'nullable|string',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'tender_status' => 'nullable|string',
        ]);
        $performanceBond->update($payload);
        return Response::json($performanceBond);
    }

    public function deletePerformanceBond(PerformanceBond $performanceBond)
    {
        $performanceBond->delete();
        return Response::json(['message' => 'Deleted successfully']);
    }
}
