<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Links one cash_in_hand_entries row to one bank_entries row as a single
// logical Bank<->Cash transfer (see OfficeController::createAccountTransfer).
// A shared UUID rather than each other's own auto-increment id, since those
// are independent per-table sequences and could coincidentally collide -
// this keeps "these two rows are the same transfer" unambiguous.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_in_hand_entries', function (Blueprint $table) {
            $table->uuid('linked_transfer_id')->nullable()->after('balance')->index();
        });
        Schema::table('bank_entries', function (Blueprint $table) {
            $table->uuid('linked_transfer_id')->nullable()->after('balance')->index();
        });
    }

    public function down(): void
    {
        Schema::table('cash_in_hand_entries', function (Blueprint $table) {
            $table->dropColumn('linked_transfer_id');
        });
        Schema::table('bank_entries', function (Blueprint $table) {
            $table->dropColumn('linked_transfer_id');
        });
    }
};
