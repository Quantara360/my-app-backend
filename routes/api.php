<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfficeController;
use Illuminate\Support\Facades\Route;

// Public routes with rate limiting (30 requests per 1 minute)
Route::middleware('throttle:30,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('profile', [AuthController::class, 'updateProfile']);

    Route::get('worksites', [OfficeController::class, 'worksites']);
    Route::get('worksites/{worksite}', [OfficeController::class, 'worksite']);
    Route::post('worksites', [OfficeController::class, 'createWorksite']);
    Route::put('worksites/{worksite}', [OfficeController::class, 'updateWorksite']);
    Route::delete('worksites/{worksite}', [OfficeController::class, 'deleteWorksite']);
    Route::post('worksites/{worksite}/reports', [OfficeController::class, 'saveWorksiteReport']);

    Route::get('hospitals', [OfficeController::class, 'hospitals']);
    Route::post('hospitals', [OfficeController::class, 'createHospital']);
    Route::put('hospitals/{hospital}', [OfficeController::class, 'updateHospital']);
    Route::delete('hospitals/{hospital}', [OfficeController::class, 'deleteHospital']);

    Route::get('sub-sites', [OfficeController::class, 'subSites']);
    Route::post('sub-sites', [OfficeController::class, 'createSubSite']);
    Route::put('sub-sites/{subSite}', [OfficeController::class, 'updateSubSite']);
    Route::delete('sub-sites/{subSite}', [OfficeController::class, 'deleteSubSite']);

    Route::get('sub-site-images', [OfficeController::class, 'getBookImages']);
    Route::post('sub-site-images/upload', [OfficeController::class, 'uploadBookImage']);
    Route::delete('sub-site-images/{image}', [OfficeController::class, 'deleteBookImage']);

    Route::get('workers', [OfficeController::class, 'workers']);
    Route::post('workers', [OfficeController::class, 'createWorker']);
    Route::put('workers/{worker}', [OfficeController::class, 'updateWorker']);
    Route::delete('workers/{worker}', [OfficeController::class, 'deleteWorker']);
    Route::get('workers/{worker}/epf-history', [OfficeController::class, 'workerEpfHistory']);
    Route::get('assets', [OfficeController::class, 'assets']);
    Route::post('assets', [OfficeController::class, 'createAsset']);
    Route::put('assets/{asset}', [OfficeController::class, 'updateAsset']);
    Route::delete('assets/{asset}', [OfficeController::class, 'deleteAsset']);
    Route::get('machineries', [OfficeController::class, 'machineries']);
    Route::post('machineries', [OfficeController::class, 'createMachinery']);
    Route::put('machineries/{machinery}', [OfficeController::class, 'updateMachinery']);
    Route::delete('machineries/{machinery}', [OfficeController::class, 'deleteMachinery']);
    Route::get('chemicals', [OfficeController::class, 'chemicals']);
    Route::post('chemicals', [OfficeController::class, 'createChemical']);
    Route::put('chemicals/{chemical}', [OfficeController::class, 'updateChemical']);
    Route::delete('chemicals/{chemical}', [OfficeController::class, 'deleteChemical']);
    Route::get('approvals', [OfficeController::class, 'approvals']);
    Route::post('approvals', [OfficeController::class, 'createApproval']);
    Route::put('approvals/{approval}', [OfficeController::class, 'updateApproval']);
    Route::delete('approvals/{approval}', [OfficeController::class, 'deleteApproval']);
    Route::get('worker-salaries', [OfficeController::class, 'workerSalaries']);
    Route::post('worker-salaries', [OfficeController::class, 'createWorkerSalary']);
    Route::put('worker-salaries/{workerSalary}', [OfficeController::class, 'updateWorkerSalary']);
    Route::delete('worker-salaries/{workerSalary}', [OfficeController::class, 'deleteWorkerSalary']);
    Route::get('office-salaries', [OfficeController::class, 'officeSalaries']);
    Route::post('office-salaries', [OfficeController::class, 'createOfficeSalary']);
    Route::put('office-salaries/{officeSalary}', [OfficeController::class, 'updateOfficeSalary']);
    Route::delete('office-salaries/{officeSalary}', [OfficeController::class, 'deleteOfficeSalary']);
    Route::get('peticash', [OfficeController::class, 'peticash']);
    Route::post('peticash', [OfficeController::class, 'createPeticash']);
    Route::put('peticash/{peticashTransaction}', [OfficeController::class, 'updatePeticash']);
    Route::delete('peticash/{peticashTransaction}', [OfficeController::class, 'deletePeticash']);
    
    Route::get('other-payments', [OfficeController::class, 'otherPayments']);
    Route::post('other-payments', [OfficeController::class, 'createOtherPayment']);
    Route::put('other-payments/{otherPayment}', [OfficeController::class, 'updateOtherPayment']);
    Route::delete('other-payments/{otherPayment}', [OfficeController::class, 'deleteOtherPayment']);
    
    // Personal Assets Routes
    Route::get('vehicles', [OfficeController::class, 'vehicles']);
    Route::post('vehicles', [OfficeController::class, 'createVehicle']);
    Route::put('vehicles/{vehicle}', [OfficeController::class, 'updateVehicle']);
    Route::delete('vehicles/{vehicle}', [OfficeController::class, 'deleteVehicle']);
    
    Route::get('jewelleries', [OfficeController::class, 'jewelleries']);
    Route::post('jewelleries', [OfficeController::class, 'createJewellery']);
    Route::put('jewelleries/{jewellery}', [OfficeController::class, 'updateJewellery']);
    Route::delete('jewelleries/{jewellery}', [OfficeController::class, 'deleteJewellery']);
    
    Route::get('properties', [OfficeController::class, 'properties']);
    Route::post('properties', [OfficeController::class, 'createProperty']);
    Route::put('properties/{property}', [OfficeController::class, 'updateProperty']);
    Route::delete('properties/{property}', [OfficeController::class, 'deleteProperty']);
    
    // Approval Actions
    Route::patch('approvals/{approval}/approve', [OfficeController::class, 'approveApproval']);
    Route::put('workers/{worker}/terminate', [OfficeController::class, 'terminateWorker']);

    // Face Recognition – worker photo upload (office staff)
    Route::post('workers/{worker}/upload-face', [OfficeController::class, 'uploadWorkerFace']);

    // Face Recognition – supervisor marks attendance via camera
    Route::post('face-recognition/recognize', [OfficeController::class, 'recognizeFace']);

    // Attendance
    Route::get('attendances', [OfficeController::class, 'attendances']);
    Route::post('attendances', [OfficeController::class, 'markAttendance']);
    Route::delete('attendances/{attendance}', [OfficeController::class, 'deleteAttendance']);

    // Personal Documents (Notes and Files)
    Route::get('personal-notes', [\App\Http\Controllers\PersonalDocumentsController::class, 'getNotes']);
    Route::post('personal-notes', [\App\Http\Controllers\PersonalDocumentsController::class, 'storeNote']);
    Route::put('personal-notes/{id}', [\App\Http\Controllers\PersonalDocumentsController::class, 'updateNote']);
    Route::delete('personal-notes/{id}', [\App\Http\Controllers\PersonalDocumentsController::class, 'deleteNote']);

    Route::get('personal-files', [\App\Http\Controllers\PersonalDocumentsController::class, 'getFiles']);
    Route::post('personal-files', [\App\Http\Controllers\PersonalDocumentsController::class, 'storeFile']);
    Route::put('personal-files/{id}', [\App\Http\Controllers\PersonalDocumentsController::class, 'updateFile']);
    Route::delete('personal-files/{id}', [\App\Http\Controllers\PersonalDocumentsController::class, 'deleteFile']);
});
