<?php

use App\Support\EmailAddress;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_canonical')->nullable()->after('email'); // see App\Support\EmailAddress
        });

        DB::table('users')->select('id', 'email')->orderBy('id')->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['email_canonical' => EmailAddress::canonical($user->email)]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email_canonical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email_canonical']);
            $table->dropColumn('email_canonical');
        });
    }
};
