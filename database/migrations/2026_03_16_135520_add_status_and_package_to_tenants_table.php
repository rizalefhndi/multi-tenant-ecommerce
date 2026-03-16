<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->string('status')->default('pending')->after('data');
            }

            if (!Schema::hasColumn('tenants', 'package_id')) {
                $table->foreignId('package_id')->nullable()->after('status')->constrained('packages')->nullOnDelete();
            }

            if (!Schema::hasColumn('tenants', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('package_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'package_id')) {
                $table->dropForeign(['package_id']);
            }

            $columnsToDrop = [];
            if (Schema::hasColumn('tenants', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('tenants', 'package_id')) {
                $columnsToDrop[] = 'package_id';
            }
            if (Schema::hasColumn('tenants', 'expired_at')) {
                $columnsToDrop[] = 'expired_at';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
