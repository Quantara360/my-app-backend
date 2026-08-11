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
use App\Models\CashInHandEntry;
use App\Models\BankEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class OfficeController extends Controller
{
    // ─── Public Endpoints (for Registration) ──────────────────────────────────
    
    public function publicWorksites(Request $request)
    {
        $query = Worksite::query();
        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }
        return Response::json($query->get()->map(function ($site) {
            $site->parent_id = $site->parent_id !== null && $site->parent_id !== '' ? (int) $site->parent_id : null;
            return $site;
        }));
    }

    public function publicHospitals(Request $request)
    {
        $query = Hospital::query();
        if ($request->has('worksite_id')) {
            $query->where('worksite_id', $request->query('worksite_id'));
        }
        return Response::json($query->get());
    }

    // ─── Protected Endpoints ──────────────────────────────────────────────────

    public function worksites(Request $request)
    {
        $user = $request->user();
        $query = Worksite::query();

        // Supervisor scope: restrict to their assigned main site
        if ($user && $user->role === 'supervisor' && $user->worksite_id) {
            $query->where('id', $user->worksite_id);
        } else {
            if ($request->has('parent_id')) {
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
        $user = $request->user();
        $query = Hospital::query();

        if ($request->has('worksite_id')) {
            $query->where('worksite_id', $request->query('worksite_id'));
        }

        // Supervisor scope: if user has hospital restrictions, apply them
        if ($user && $user->role === 'supervisor') {
            $user->loadMissing('hospitals');
            $hospitalIds = $user->hospitals->pluck('id');
            if ($hospitalIds->isNotEmpty()) {
                // Only show the hospitals they are scoped to
                $query->whereIn('id', $hospitalIds);
            } elseif ($user->worksite_id) {
                // No specific hospitals — show all hospitals in their worksite
                $query->where('worksite_id', $user->worksite_id);
            }
            // If neither: supervisor has no scope restriction, show all (backward compat)
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
        $user = $request->user();
        $query = SubSite::query();

        if ($request->has('hospital_id')) {
            $query->where('hospital_id', $request->query('hospital_id'));
        }

        // Supervisor scope: restrict subsites to visible hospitals
        if ($user && $user->role === 'supervisor') {
            $user->loadMissing('hospitals');
            $hospitalIds = $user->hospitals->pluck('id');
            if ($hospitalIds->isNotEmpty()) {
                $query->whereIn('hospital_id', $hospitalIds);
            } elseif ($user->worksite_id) {
                // Scope to all hospitals under their worksite
                $allowedHospitalIds = Hospital::where('worksite_id', $user->worksite_id)->pluck('id');
                $query->whereIn('hospital_id', $allowedHospitalIds);
            }
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
        // Supports two scoping modes:
        //   sub_site_id  → images for a specific sub-site (e.g. Castle)
        //   worksite_id  → images for a worksite that has no sub-sites (e.g. Clean)
        // This prevents collision when worksites.id and sub_sites.id share numbers.
        $hasSubSite  = $request->has('sub_site_id') && $request->query('sub_site_id') !== null;
        $hasWorksite = $request->has('worksite_id') && $request->query('worksite_id') !== null;

        if (!$hasSubSite && !$hasWorksite) {
            return Response::json(['error' => 'Either sub_site_id or worksite_id is required.'], 422);
        }

        if ($hasSubSite) {
            $query = SubSiteImage::where('sub_site_id', $request->query('sub_site_id'));
            // Also scope by worksite_id if provided — prevents collision when
            // sub-sites of different worksites share the same numeric sub_site_id
            if ($request->has('worksite_id') && $request->query('worksite_id') !== null) {
                $query->where('worksite_id', $request->query('worksite_id'));
            } else {
                $query->whereNull('worksite_id');
            }
        } else {
            $query = SubSiteImage::where('worksite_id', $request->query('worksite_id'))
                                 ->whereNull('sub_site_id');
        }

        if ($request->has('book_id')) {
            $query->where('book_id', $request->query('book_id'));
        }

        return Response::json($query->latest()->get());
    }

    public function uploadBookImage(Request $request)
    {
        // Accept either sub_site_id (sub-site scope) or worksite_id (worksite scope)
        $request->validate([
            'sub_site_id' => 'nullable|integer',
            'worksite_id' => 'nullable|integer',
            'book_id'     => 'required|integer',
            'photo'       => 'required|image|max:10240',
        ]);

        $subSiteId  = $request->input('sub_site_id');
        $worksiteId = $request->input('worksite_id');
        $bookId     = $request->input('book_id');

        if (!$subSiteId && !$worksiteId) {
            return Response::json(['error' => 'Either sub_site_id or worksite_id is required.'], 422);
        }

        // Count total images for this exact scope (per book, not per 24h window)
        $countQuery = SubSiteImage::where('book_id', $bookId);
        if ($subSiteId) {
            $countQuery->where('sub_site_id', $subSiteId);
            if ($worksiteId) {
                $countQuery->where('worksite_id', $worksiteId);
            } else {
                $countQuery->whereNull('worksite_id');
            }
        } else {
            $countQuery->where('worksite_id', $worksiteId)->whereNull('sub_site_id');
        }

        if ($countQuery->count() >= 10) {
            return Response::json(['error' => 'Maximum limit of 10 images reached for this book.'], 422);
        }

        $scopeFolder = $subSiteId ? "sub_{$subSiteId}" : "worksite_{$worksiteId}";
        $path = $request->file('photo')->store("sub_site_images/{$scopeFolder}/book_{$bookId}", 'public');

        $image = SubSiteImage::create([
            'sub_site_id' => $subSiteId ?: null,
            // Always store worksite_id — when both are provided it lets us
            // distinguish images from sub-sites of different worksites that
            // happen to share the same numeric sub_site_id
            'worksite_id' => $worksiteId ?: null,
            'book_id'     => $bookId,
            'image_path'  => $path,
        ]);

        return Response::json($image, 201);
    }

    public function deleteBookImage(SubSiteImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return Response::json(['message' => 'Deleted successfully']);
    }

    public function workers(Request $request)
    {
        $query = Worker::with(['worksite', 'epfHistories']);

        if ($request->filled('worksite_id')) {
            $query->where('assigned_worksite_id', $request->worksite_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('all')) {
            return Response::json($query->get());
        }

        return Response::json($query->paginate(50));
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
        // config(), not env() directly - env() outside config/*.php returns
        // null once config is cached (php artisan config:cache), which would
        // silently break face recognition in production.
        $this->faceServiceUrl = config('services.face_service.url');
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
        // NOTE: CPU-only ArcFace/MTCNN inference on this machine measured
        // ~17s per call even warm - 30s left almost no headroom before a
        // slightly larger photo or a queued request (Flask dev server is
        // single-threaded) tipped it into a client-side timeout.
        try {
            $fsResponse = Http::timeout(60)->post("{$this->faceServiceUrl}/register", [
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
        } catch (\Throwable $e) {
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
            $fsResponse = Http::timeout(60)->post("{$this->faceServiceUrl}/recognize", [
                'image_base64' => $request->input('image_base64'),
            ]);

            // $fsResponse->json() is null when the AI service's body wasn't
            // valid JSON (e.g. a proxy error page) - fall back to a
            // {success,error} shape so the frontend never has to guess.
            $body = $fsResponse->json();
            if ($body === null) {
                return Response::json([
                    'success' => false,
                    'error'   => "Face service returned an unexpected response (HTTP {$fsResponse->status()})",
                ], 502);
            }
            return Response::json($body, $fsResponse->status());
        } catch (\Throwable $e) {
            // \Throwable (not \Exception) so a TypeError/Error from a bad
            // config or malformed response also produces a proper JSON body
            // instead of falling through to the framework's generic handler.
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

        // Debug: log incoming data (debug level so it doesn't spam production logs)
        \Log::debug('[markAttendance] incoming', $request->all());

        try {
            $payload = $request->validate([
                'worker_id'   => 'required|exists:workers,id',
                'worksite_id' => 'nullable',           // accept anything — we override below
                'hospital_id' => 'nullable|exists:hospitals,id',
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

        // Auto-derive hospital_id from sub_site if not explicitly provided
        if (empty($payload['hospital_id']) && !empty($payload['sub_site_id'])) {
            $subSite = \App\Models\SubSite::find($payload['sub_site_id']);
            if ($subSite) {
                $payload['hospital_id'] = $subSite->hospital_id;
            }
        }

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

            // Status Logic: Present if <= 7:30 AM/PM, Late if > 7:30 AM/PM
            $status = 'present';
            $localTime = $now->copy()->timezone('Asia/Colombo');
            $hour = (int)$localTime->format('H');
            $minute = (int)$localTime->format('i');

            if ($payload['shift'] === 'Morning') {
                if ($hour > 7 || ($hour === 7 && $minute > 30)) {
                    $status = 'late';
                }
            } else { // Evening
                if ($hour > 19 || ($hour === 19 && $minute > 30)) {
                    $status = 'late';
                }
            }
            $payload['marked_at'] = $now;
            $payload['status']    = $status;
            $payload['method']    = $payload['method'] ?? 'face';

            try {
                $attendance = Attendance::create($payload);
            } catch (\Illuminate\Database\QueryException $e) {
                // Unique (worker_id, date, shift) constraint - two concurrent/
                // retried "mark IN" taps (e.g. a client that retried after a
                // slow response) raced past the $existing check above. The DB
                // already rejected the duplicate; surface the same friendly
                // message the normal check gives instead of a raw 500.
                if ((string) $e->getCode() === '23000') {
                    return Response::json([
                        'success' => false,
                        'error'   => "IN attendance already marked for the {$payload['shift']} shift."
                    ], 409);
                }
                throw $e;
            }

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
        $query = Attendance::with('worker', 'worksite', 'hospital', 'subSite')->latest('marked_at');

        if ($request->filled('worksite_id')) {
            $query->where('worksite_id', $request->worksite_id);
        }
        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }
        if ($request->filled('sub_site_id')) {
            $query->where('sub_site_id', $request->sub_site_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('date', $parts[0])
                      ->whereMonth('date', $parts[1]);
            }
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

        if ($request->boolean('all')) {
            return Response::json(['data' => $query->get()]);
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
            'bond_name' => 'nullable|string',
            'bond_number' => 'nullable|string',
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
            'bond_name' => 'nullable|string',
            'bond_number' => 'nullable|string',
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
            'bond_name' => 'nullable|string',
            'bond_number' => 'nullable|string',
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
            'bond_name' => 'nullable|string',
            'bond_number' => 'nullable|string',
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

    // ── Cash In Hand Entries ─────────────────────────────────────────────────

    public function cashInHandEntries()
    {
        return Response::json(['data' => CashInHandEntry::orderBy('date', 'asc')->get()]);
    }

    public function createCashInHandEntry(Request $request)
    {
        $payload = $request->validate([
            'date'        => 'required|date',
            'cheque_no'   => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'debit'       => 'nullable|numeric|min:0',
            'credit'      => 'nullable|numeric|min:0',
            'balance'     => 'required|numeric',
        ]);

        $entry = CashInHandEntry::create($payload);
        return Response::json(['data' => $entry], 201);
    }

    public function updateCashInHandEntry(Request $request, CashInHandEntry $cashInHandEntry)
    {
        $payload = $request->validate([
            'date'        => 'required|date',
            'cheque_no'   => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'debit'       => 'nullable|numeric|min:0',
            'credit'      => 'nullable|numeric|min:0',
            'balance'     => 'required|numeric',
        ]);

        $cashInHandEntry->update($payload);
        return Response::json(['data' => $cashInHandEntry->refresh()]);
    }

    public function deleteCashInHandEntry(CashInHandEntry $cashInHandEntry)
    {
        $cashInHandEntry->delete();
        return Response::json(['deleted' => true]);
    }

    // ── Bank Entries ─────────────────────────────────────────────────────────

    public function bankEntries()
    {
        return Response::json(['data' => BankEntry::orderBy('date', 'asc')->get()]);
    }

    public function createBankEntry(Request $request)
    {
        $payload = $request->validate([
            'date'        => 'required|date',
            'cheque_no'   => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'debit'       => 'nullable|numeric|min:0',
            'credit'      => 'nullable|numeric|min:0',
            'balance'     => 'required|numeric',
        ]);

        $entry = BankEntry::create($payload);
        return Response::json(['data' => $entry], 201);
    }

    public function updateBankEntry(Request $request, BankEntry $bankEntry)
    {
        $payload = $request->validate([
            'date'        => 'required|date',
            'cheque_no'   => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'debit'       => 'nullable|numeric|min:0',
            'credit'      => 'nullable|numeric|min:0',
            'balance'     => 'required|numeric',
        ]);

        $bankEntry->update($payload);
        return Response::json(['data' => $bankEntry->refresh()]);
    }

    public function deleteBankEntry(BankEntry $bankEntry)
    {
        $bankEntry->delete();
        return Response::json(['deleted' => true]);
    }

    // — Ledger Settings ─────────────────────────────

    public function getLedgerSetting(string $type)
    {
        $setting = \App\Models\LedgerSetting::where('ledger_type', $type)->first();
        return Response::json(['data' => [
            'manual_prev_balance' => $setting?->manual_prev_balance,
        ]]);
    }

    public function updateLedgerSetting(Request $request, string $type)
    {
        $payload = $request->validate([
            'manual_prev_balance' => 'required|numeric',
        ]);

        $setting = \App\Models\LedgerSetting::updateOrCreate(
            ['ledger_type' => $type],
            ['manual_prev_balance' => $payload['manual_prev_balance']]
        );

        return Response::json(['data' => [
            'manual_prev_balance' => $setting->manual_prev_balance,
        ]]);
    }
}
