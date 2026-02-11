<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add deactivation fields to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deactivation_remarks')) {
                $table->text('deactivation_remarks')->nullable();
            }
            if (!Schema::hasColumn('users', 'deactivated_by')) {
                $table->unsignedBigInteger('deactivated_by')->nullable();
                $table->foreign('deactivated_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable();
            }
        });

        // Remove deactivation fields from job_orders if present
        if (Schema::hasTable('job_orders')) {
            Schema::table('job_orders', function (Blueprint $table) {
                if (Schema::hasColumn('job_orders', 'deactivated_by')) {
                    try { $table->dropForeign(['deactivated_by']); } catch (\Exception $e) { }
                }
                if (Schema::hasColumn('job_orders', 'deactivation_remarks')) {
                    $table->dropColumn('deactivation_remarks');
                }
                if (Schema::hasColumn('job_orders', 'deactivated_by')) {
                    $table->dropColumn('deactivated_by');
                }
                if (Schema::hasColumn('job_orders', 'deactivated_at')) {
                    $table->dropColumn('deactivated_at');
                }
            });
        }

        // Remove deactivation fields from inventory_transfers if present
        if (Schema::hasTable('inventory_transfers')) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_transfers', 'deactivated_by')) {
                    try { $table->dropForeign(['deactivated_by']); } catch (\Exception $e) { }
                }
                if (Schema::hasColumn('inventory_transfers', 'deactivation_remarks')) {
                    $table->dropColumn('deactivation_remarks');
                }
                if (Schema::hasColumn('inventory_transfers', 'deactivated_by')) {
                    $table->dropColumn('deactivated_by');
                }
                if (Schema::hasColumn('inventory_transfers', 'deactivated_at')) {
                    $table->dropColumn('deactivated_at');
                }
            });
        }
    }

    public function down(): void
    {
        // Add back to job_orders
        if (Schema::hasTable('job_orders')) {
            Schema::table('job_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('job_orders', 'deactivation_remarks')) {
                    $table->text('deactivation_remarks')->nullable()->after('remarks');
                }
                if (!Schema::hasColumn('job_orders', 'deactivated_by')) {
                    $table->foreignId('deactivated_by')->nullable()->constrained('users')->onDelete('set null')->after('deactivation_remarks');
                }
                if (!Schema::hasColumn('job_orders', 'deactivated_at')) {
                    $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
                }
            });
        }

        // Add back to inventory_transfers
        if (Schema::hasTable('inventory_transfers')) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_transfers', 'deactivation_remarks')) {
                    $table->text('deactivation_remarks')->nullable()->after('remarks');
                }
                if (!Schema::hasColumn('inventory_transfers', 'deactivated_by')) {
                    $table->foreignId('deactivated_by')->nullable()->constrained('users')->onDelete('set null')->after('deactivation_remarks');
                }
                if (!Schema::hasColumn('inventory_transfers', 'deactivated_at')) {
                    $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
                }
            });
        }

        // Remove from users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deactivated_by')) {
                try { $table->dropForeign(['deactivated_by']); } catch (\Exception $e) { }
            }
            if (Schema::hasColumn('users', 'deactivation_remarks')) {
                $table->dropColumn('deactivation_remarks');
            }
            if (Schema::hasColumn('users', 'deactivated_by')) {
                $table->dropColumn('deactivated_by');
            }
            if (Schema::hasColumn('users', 'deactivated_at')) {
                $table->dropColumn('deactivated_at');
            }
        });
    }
};
